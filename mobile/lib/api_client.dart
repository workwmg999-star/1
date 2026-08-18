import 'dart:convert';
import 'dart:io';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;

class ApiClient {
  ApiClient({http.Client? client}) : _client = client ?? http.Client();

  // Supply your LAN address when launching on a real phone.
  static const baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api/v1',
  );

  final http.Client _client;
  final _storage = const FlutterSecureStorage();

  Future<bool> get isSignedIn async => (await _storage.read(key: 'auth_token')) != null;

  Future<void> login(String email, String password) async {
    final response = await _client.post(
      Uri.parse('$baseUrl/auth/login'),
      headers: const {'Accept': 'application/json'},
      body: {'email': email, 'password': password},
    );
    final json = _decode(response);
    if (response.statusCode != 200) throw ApiException(_message(json));
    await _storage.write(key: 'auth_token', value: json['data']['token'] as String);
  }

  Future<void> logout() async => _storage.delete(key: 'auth_token');

  Future<List<Map<String, dynamic>>> documents() async {
    final response = await _client.get(Uri.parse('$baseUrl/documents'), headers: await _headers());
    final json = _decode(response);
    if (response.statusCode != 200) throw ApiException(_message(json));
    return List<Map<String, dynamic>>.from(json['data'] as List);
  }

  Future<List<Map<String, dynamic>>> folders() async {
    final response = await _client.get(Uri.parse('$baseUrl/folders'), headers: await _headers());
    final json = _decode(response);
    if (response.statusCode != 200) throw ApiException(_message(json));
    return List<Map<String, dynamic>>.from(json['data'] as List);
  }

  Future<void> uploadScan({required File file, required String title, int? folderId}) async {
    final request = http.MultipartRequest('POST', Uri.parse('$baseUrl/documents'));
    request.headers.addAll(await _headers());
    request.fields['title'] = title;
    if (folderId != null) request.fields['folder_id'] = '$folderId';
    request.files.add(await http.MultipartFile.fromPath('file', file.path));
    final streamed = await request.send();
    final response = await http.Response.fromStream(streamed);
    if (response.statusCode != 201) throw ApiException(_message(_decode(response)));
  }

  Future<Map<String, String>> _headers() async {
    final token = await _storage.read(key: 'auth_token');
    return {'Accept': 'application/json', if (token != null) 'Authorization': 'Bearer $token'};
  }

  Map<String, dynamic> _decode(http.Response response) {
    try { return jsonDecode(response.body) as Map<String, dynamic>; } catch (_) { return {}; }
  }

  String _message(Map<String, dynamic> json) {
    if (json['message'] is String) return json['message'] as String;
    final errors = json['errors'];
    if (errors is Map && errors.isNotEmpty) {
      final first = errors.values.first;
      if (first is List && first.isNotEmpty) return first.first.toString();
    }
    return 'تعذر إتمام الطلب.';
  }
}

class ApiException implements Exception {
  ApiException(this.message);
  final String message;
  @override
  String toString() => message;
}
