# Kawaii Identicon Generator

🇬🇧 **English follows Japanese.**  
🇯🇵 **日本語説明は英語の後に続きます。**

## 🌸 Overview (概要)

![Image](https://github.com/user-attachments/assets/6b722763-db9c-4d29-bbae-ff15030331cc)

This is a **PHP library** for generating **Kawaii Identicons**.  
Each identicon is uniquely created based on a **hash of a given ID**.  
The generated images are output as **Base64-encoded PNGs**, making them easy to embed in web applications.

これは **PHPライブラリ** で、**Kawaii Identicon（かわいい識別アイコン）** を生成するものです。  
各アイコンは **指定したIDのハッシュ** をもとにユニークに作成されます。  
生成された画像は **Base64エンコードされたPNG** として出力されるため、Webアプリケーションに簡単に埋め込むことができます。

## 🚀 Features (特徴)

- Generates **unique** identicons based on a hash of an input string.  
- Outputs **transparent PNGs** encoded in **Base64**.  
- Uses **PHP GD Library** for high-quality rendering.  
- **No dependencies**, works with native PHP.  

- **入力文字列のハッシュ** を基に **ユニークなIdenticon** を生成。  
- **透過PNG** の **Base64エンコード** で出力。  
- **PHPのGDライブラリ** を使用し、高品質なレンダリング。  
- **依存ライブラリなし**、ネイティブなPHPのみで動作。  

## 📌 Requirements (動作環境)

- PHP 7.4+  
- GD Library (`php-gd` extension enabled)  

- **PHP 7.4以上**  
- **GDライブラリ** が有効（`php-gd` 拡張機能）  

## 🔧 Installation (インストール)

1. Clone this repository:  
   リポジトリをクローンします：  
   ```sh
   git clone https://github.com/styleio/kawaii_identicon.git
   cd kawaii_identicon
   ```
2. Ensure the **GD Library** is enabled in your PHP environment.  
   PHP環境で **GDライブラリ** が有効になっていることを確認してください。  

## 🏃 Usage (使い方)

### **1️⃣ Basic Usage (基本的な使い方)**

```php
require_once __DIR__ . '/src/kawaii_identicon.php';

// Generate an Identicon for a unique ID
$userId = 'example_user_123';
$iconBase64 = KawaiiIdenticon::get($userId, 300);

// Output as an image
echo '<img src="data:image/png;base64,' . $iconBase64 . '" alt="Kawaii Icon" />';
```

### **2️⃣ Running a Local PHP Server (ローカルで実行する場合)**

Run the following command and open **http://localhost:8000/** in your browser:  
次のコマンドを実行し、**http://localhost:8000/** を開きます。  

```sh
php -S localhost:8000
```

## 📂 File Structure (ファイル構成)

```
/kawaii-identicon-generator
 ├── index.php            # Example usage file (サンプル使用例)
 ├── src/
 │   ├── kawaii_identicon.php # Identicon generator class (アイコン生成クラス)
 ├── README.md            # Documentation (ドキュメント)
```

## 📜 License (ライセンス)

This project is licensed under the **MIT License**.  
本プロジェクトは **MITライセンス** のもとで提供されます。  

---

✨ **Enjoy generating unique Kawaii Identicons!** 🎨  
✨ **ユニークなKawaii Identiconを楽しんでください！** 🎨  
