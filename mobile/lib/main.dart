import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_cropper/image_cropper.dart';
import 'package:image_picker/image_picker.dart';
import 'package:path_provider/path_provider.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;

import 'api_client.dart';

void main() => runApp(const DocuScanApp());

class DocuScanApp extends StatelessWidget {
  const DocuScanApp({super.key});

  @override
  Widget build(BuildContext context) => MaterialApp(
        title: 'DocuScan',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xff6757f5)),
          scaffoldBackgroundColor: const Color(0xfff8f7fc),
          useMaterial3: true,
        ),
        home: const AppGate(),
      );
}

class AppGate extends StatefulWidget {
  const AppGate({super.key});
  @override
  State<AppGate> createState() => _AppGateState();
}

class _AppGateState extends State<AppGate> {
  final api = ApiClient();
  late final Future<bool> _signedIn = api.isSignedIn;

  @override
  Widget build(BuildContext context) => FutureBuilder<bool>(
        future: _signedIn,
        builder: (_, snapshot) {
          if (!snapshot.hasData) return const Scaffold(body: Center(child: CircularProgressIndicator()));
          return snapshot.data! ? HomePage(api: api) : LoginPage(api: api);
        },
      );
}

class LoginPage extends StatefulWidget {
  const LoginPage({super.key, required this.api});
  final ApiClient api;
  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final email = TextEditingController();
  final password = TextEditingController();
  bool busy = false;

  Future<void> _login() async {
    setState(() => busy = true);
    try {
      await widget.api.login(email.text.trim(), password.text);
      if (mounted) Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => HomePage(api: widget.api)));
    } on ApiException catch (e) {
      if (mounted) _notice(e.message);
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  void _notice(String text) => ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(text)));

  @override
  Widget build(BuildContext context) => Scaffold(
        body: SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(28),
            child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
              const Spacer(),
              const Icon(Icons.document_scanner_rounded, size: 70, color: Color(0xff6757f5)),
              const SizedBox(height: 18),
              Text('Smart Document Scanner', textAlign: TextAlign.center, style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              const Text('امسح، عدّل واحفظ مستنداتك فورًا', textAlign: TextAlign.center),
              const SizedBox(height: 40),
              TextField(controller: email, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(labelText: 'البريد الإلكتروني', border: OutlineInputBorder())),
              const SizedBox(height: 12),
              TextField(controller: password, obscureText: true, onSubmitted: (_) => _login(), decoration: const InputDecoration(labelText: 'كلمة المرور', border: OutlineInputBorder())),
              const SizedBox(height: 18),
              FilledButton(onPressed: busy ? null : _login, child: Padding(padding: const EdgeInsets.all(14), child: busy ? const CircularProgressIndicator() : const Text('تسجيل الدخول'))),
              const Spacer(),
            ]),
          ),
        ),
      );
}

class HomePage extends StatefulWidget {
  const HomePage({super.key, required this.api});
  final ApiClient api;
  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  late Future<List<Map<String, dynamic>>> _documents;
  late Future<List<Map<String, dynamic>>> _folders;

  @override
  void initState() { super.initState(); _reload(); }
  void _reload() => setState(() { _documents = widget.api.documents(); _folders = widget.api.folders(); });

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: const Text('مستنداتي'), actions: [IconButton(onPressed: _reload, icon: const Icon(Icons.refresh))]),
        floatingActionButton: FloatingActionButton.extended(
          onPressed: () async { await Navigator.push(context, MaterialPageRoute(builder: (_) => ScanPage(api: widget.api, folders: _folders))); _reload(); },
          icon: const Icon(Icons.camera_alt_rounded), label: const Text('مسح'),
        ),
        body: RefreshIndicator(
          onRefresh: () async => _reload(),
          child: FutureBuilder<List<Map<String, dynamic>>>(
            future: _documents,
            builder: (_, snapshot) {
              if (snapshot.connectionState != ConnectionState.done) return const Center(child: CircularProgressIndicator());
              if (snapshot.hasError) return ListView(children: [const SizedBox(height: 160), Center(child: Text('تعذر تحميل المستندات: ${snapshot.error}'))]);
              final docs = snapshot.data ?? [];
              return ListView(padding: const EdgeInsets.all(16), children: [
                Text('آخر المستندات', style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold)),
                const SizedBox(height: 12),
                if (docs.isEmpty) const _EmptyDocuments(),
                ...docs.map((doc) => Card(child: ListTile(leading: const Icon(Icons.description_outlined), title: Text('${doc['title']}'), subtitle: Text('${doc['file_type'] ?? 'image'} · ${doc['pages_count'] ?? 1} صفحة'))),
              ]);
            },
          ),
        ),
      );
}

class _EmptyDocuments extends StatelessWidget {
  const _EmptyDocuments();
  @override
  Widget build(BuildContext context) => const Padding(padding: EdgeInsets.only(top: 120), child: Center(child: Column(children: [Icon(Icons.folder_open_outlined, size: 64), SizedBox(height: 12), Text('لا توجد مستندات بعد'), Text('اضغط «مسح» لالتقاط أول مستند.')])));
}

class ScanPage extends StatefulWidget {
  const ScanPage({super.key, required this.api, required this.folders});
  final ApiClient api;
  final Future<List<Map<String, dynamic>>> folders;
  @override
  State<ScanPage> createState() => _ScanPageState();
}

class _ScanPageState extends State<ScanPage> {
  final picker = ImagePicker();
  File? scan;
  bool saving = false;
  int? folderId;
  String format = 'jpg';

  Future<void> _pick(ImageSource source) async {
    final picked = await picker.pickImage(source: source, imageQuality: 95, maxWidth: 2400);
    if (picked == null) return;
    // Native crop UI supports rotation and free-form manual correction after camera/gallery input.
    final cropped = await ImageCropper().cropImage(
      sourcePath: picked.path,
      compressFormat: ImageCompressFormat.jpg,
      compressQuality: 92,
      uiSettings: [AndroidUiSettings(toolbarTitle: 'ضبط حدود المستند', lockAspectRatio: false), IOSUiSettings(title: 'ضبط حدود المستند')],
    );
    if (cropped != null) setState(() => scan = File(cropped.path));
  }

  Future<void> _save() async {
    if (scan == null) return;
    final title = await _askTitle();
    if (title == null || title.trim().isEmpty) return;
    setState(() => saving = true);
    try {
      final file = format == 'pdf' ? await _asPdf(scan!) : scan!;
      await widget.api.uploadScan(file: file, title: title.trim(), folderId: folderId);
      if (mounted) { ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('تم حفظ المستند.'))); Navigator.pop(context); }
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    } finally { if (mounted) setState(() => saving = false); }
  }

  Future<File> _asPdf(File image) async {
    final document = pw.Document();
    final bytes = await image.readAsBytes();
    final picture = pw.MemoryImage(bytes);
    document.addPage(pw.Page(pageFormat: PdfPageFormat.a4, build: (_) => pw.Center(child: pw.Image(picture, fit: pw.BoxFit.contain))));
    final output = File('${(await getTemporaryDirectory()).path}/scan_${DateTime.now().millisecondsSinceEpoch}.pdf');
    return output.writeAsBytes(await document.save());
  }

  Future<String?> _askTitle() async {
    final controller = TextEditingController(text: 'Scan_${DateTime.now().toIso8601String().substring(0, 10)}');
    return showDialog<String>(context: context, builder: (_) => AlertDialog(title: const Text('اسم المستند'), content: TextField(controller: controller, autofocus: true), actions: [TextButton(onPressed: () => Navigator.pop(context), child: const Text('إلغاء')), FilledButton(onPressed: () => Navigator.pop(context, controller.text), child: const Text('حفظ'))]));
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: const Text('مسح مستند')),
        body: SafeArea(child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
            Expanded(
              child: DecoratedBox(
                decoration: BoxDecoration(color: const Color(0xff17171b), borderRadius: BorderRadius.circular(28)),
                child: Stack(children: [
                  Positioned.fill(
                    child: scan == null
                        ? const Center(child: Column(mainAxisSize: MainAxisSize.min, children: [Icon(Icons.document_scanner_outlined, color: Colors.white, size: 72), SizedBox(height: 12), Text('وجّه الكاميرا نحو المستند', style: TextStyle(color: Colors.white, fontSize: 17)), SizedBox(height: 5), Text('اختر مكانًا بإضاءة جيدة', style: TextStyle(color: Colors.white60))]))
                        : ClipRRect(borderRadius: BorderRadius.circular(28), child: Image.file(scan!, width: double.infinity, fit: BoxFit.contain)),
                  ),
                  const Positioned(top: 18, left: 18, child: CircleAvatar(backgroundColor: Colors.white, child: Icon(Icons.arrow_back, color: Colors.black))),
                  const Positioned(top: 18, right: 18, child: CircleAvatar(backgroundColor: Colors.white, child: Icon(Icons.flash_auto_outlined, color: Colors.black))),
                  if (scan != null)
                    const Positioned.fill(
                      child: IgnorePointer(
                        child: Padding(
                          padding: EdgeInsets.all(34),
                          child: DecoratedBox(decoration: BoxDecoration(border: Border.fromBorderSide(BorderSide(color: Color(0xffc7ef58), width: 2)), borderRadius: BorderRadius.all(Radius.circular(26)))),
                        ),
                      ),
                    ),
                ]),
              ),
            ),
            const SizedBox(height: 14),
            FutureBuilder<List<Map<String, dynamic>>>(future: widget.folders, builder: (_, snap) => DropdownButtonFormField<int?>(value: folderId, decoration: const InputDecoration(labelText: 'المجلد (اختياري)', border: OutlineInputBorder()), items: [const DropdownMenuItem<int?>(value: null, child: Text('بدون مجلد')), ...(snap.data ?? []).map((f) => DropdownMenuItem(value: f['id'] as int?, child: Text('${f['name']}')))], onChanged: (value) => setState(() => folderId = value))),
            const SizedBox(height: 12),
            SegmentedButton<String>(segments: const [ButtonSegment(value: 'jpg', icon: Icon(Icons.image_outlined), label: Text('JPG')), ButtonSegment(value: 'pdf', icon: Icon(Icons.picture_as_pdf_outlined), label: Text('PDF'))], selected: {format}, onSelectionChanged: (choice) => setState(() => format = choice.first)),
            const SizedBox(height: 12),
            Row(children: [Expanded(child: OutlinedButton.icon(onPressed: () => _pick(ImageSource.gallery), icon: const Icon(Icons.photo_library_outlined), label: const Text('اختر صورة'))), const SizedBox(width: 10), Expanded(child: FilledButton.icon(onPressed: () => _pick(ImageSource.camera), icon: const Icon(Icons.camera_alt), label: const Text('الكاميرا')))]),
            const SizedBox(height: 10),
            FilledButton.icon(onPressed: scan == null || saving ? null : _save, icon: const Icon(Icons.cloud_upload_outlined), label: Padding(padding: const EdgeInsets.all(10), child: Text(saving ? 'جارٍ الحفظ…' : 'حفظ في المستندات'))),
          ]),
        )),
      );
}
