import 'package:flutter_test/flutter_test.dart';
import 'package:scanner_app/main.dart';

void main() {
  testWidgets('App loads', (WidgetTester tester) async {
    await tester.pumpWidget(const ScannerApp());
    await tester.pump();
  });
}
