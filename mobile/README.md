# DocuScan Mobile

Native Flutter client for the Laravel API. It provides login, a document list, camera/gallery selection, native crop/rotation, folder selection, and authenticated upload to `/api/v1/documents`.

## Setup

Install the Flutter SDK, then create the platform runners once:

```powershell
cd mobile
flutter create . --platforms=android,ios
flutter pub get
```

For a physical Android phone on the same Wi-Fi network, use the computer's LAN address (not `localhost`):

```powershell
flutter run --dart-define=API_BASE_URL=http://192.168.8.101:8000/api/v1
```

For Android HTTP development URLs, set `android:usesCleartextTraffic="true"` in `android/app/src/main/AndroidManifest.xml`. Use HTTPS for a production server.

`image_cropper` supplies the native manual adjustment UI after both camera and gallery selection. The Laravel API remains the sole owner of authentication, folders, document metadata, and uploads.
