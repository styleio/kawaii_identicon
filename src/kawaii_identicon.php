<?php

/**
 * KawaiiIdenticon
 * https://github.com/styleio/kawaii_identicon
 *
 * Copyright (c) 2024 Sun Suzuki
 * 
 * A simple and cute Identicon generator library in PHP.
 * Generates a unique facial Identicon based on a hash of the user ID.
 *
 * Features:
 * - Generates unique Identicons
 * - Supports transparent PNG output
 * - High-quality anti-aliasing with oversampling
 *
 * Usage:
 * $iconBase64 = KawaiiIdenticon::get('example_id', 300);
 * echo '<img src="data:image/png;base64,' . $iconBase64 . '" />';
 *
 * @author  Sun Suzuki (styleio)
 * @license MIT License
 * @version 1.0.0
 */

class KawaiiIdenticon
{
    public static function get($id, $size = 300)
    {
        $hash  = md5($id);

        // ------------------------------------------------
        // 1) オーバーサンプリング用のスケール設定
        //    2〜4倍程度が目安。大きいほど仕上がりは綺麗ですが処理負荷も増えます。
        // ------------------------------------------------
        $scale     = 4;
        $largeSize = $size * $scale;

        // ------------------------------------------------
        // 2) 大きいキャンバス(透過PNG)を作成
        // ------------------------------------------------
        $imgLarge = imagecreatetruecolor($largeSize, $largeSize);
        imagealphablending($imgLarge, false);
        imagesavealpha($imgLarge, true);

        // 透明色で塗りつぶし
        $transparent = imagecolorallocatealpha($imgLarge, 0, 0, 0, 127);
        imagefilledrectangle($imgLarge, 0, 0, $largeSize, $largeSize, $transparent);

        // 中心座標や半径などをスケール対応
        $cxLarge = $largeSize / 2;
        $cyLarge = $largeSize / 2;
        $rLarge  = $largeSize * 0.48;  // 顔の円半径

        // -------- [R,G,B]配列 --------
        $baseColor   = self::createColorFromHash($hash, 0);
        $accentColor = self::createColorFromHash($hash, 3);
        $partsColor  = [40, 40, 40]; // 目・口は固定

        // -------- カラーリソース(int)を取得 --------
        $colBase   = imagecolorallocate($imgLarge, $baseColor[0],   $baseColor[1],   $baseColor[2]);
        $colAccent = imagecolorallocate($imgLarge, $accentColor[0], $accentColor[1], $accentColor[2]);
        $colParts  = imagecolorallocate($imgLarge, $partsColor[0],  $partsColor[1],  $partsColor[2]);

        // (1) 顔の円を描画 (拡大キャンバスに)
        imagefilledellipse($imgLarge, $cxLarge, $cyLarge, $rLarge * 2, $rLarge * 2, $colBase);

        // (2) アクセント用シェイプ
        $shapeType = hexdec($hash[0]) % 3;
        $offsetX   = (hexdec($hash[1]) % 21 - 10) / 10.0;
        $offsetY   = (hexdec($hash[2]) % 21 - 10) / 10.0;
        $shapeCx   = $cxLarge + $offsetX * $rLarge; // 拡大版
        $shapeCy   = $cyLarge + $offsetY * $rLarge;
        $shapeSize = $rLarge * 2;
        self::drawAccentShape($imgLarge, $shapeType, $shapeCx, $shapeCy, $shapeSize, $colAccent);

        // (3) 顔パーツ
        $faceOffsetX = (hexdec($hash[4]) % 21 - 10) / 10.0;
        $faceOffsetY = (hexdec($hash[5]) % 21 - 10) / 10.0;
        $faceCx = $cxLarge + $faceOffsetX * $rLarge * 0.1;
        $faceCy = $cyLarge + $faceOffsetY * $rLarge * 0.1;

        $eyeShape   = hexdec($hash[6]) % 3;
        $mouthShape = hexdec($hash[7]) % 3;

        self::drawFace($imgLarge, $faceCx, $faceCy, $rLarge, $eyeShape, $mouthShape, $colParts);

        // (4) 円マスク（拡大キャンバス上で実行）
        self::applyCircleMask($imgLarge, $cxLarge, $cyLarge, $rLarge, $transparent, $largeSize);

        // ------------------------------------------------
        // 3) 縮小して最終サイズの画像を作成
        // ------------------------------------------------
        $imgFinal = imagecreatetruecolor($size, $size);
        imagealphablending($imgFinal, false);
        imagesavealpha($imgFinal, true);

        // GD の高品質なリサンプリングで縮小コピー
        imagecopyresampled(
            $imgFinal,
            $imgLarge,
            0,
            0,    // コピー先(最終画像)の開始座標
            0,
            0,    // コピー元(拡大画像)の開始座標
            $size,
            $size,        // コピー先の幅・高さ
            $largeSize,
            $largeSize // コピー元の幅・高さ
        );

        // 出力バイナリを生成してBase64エンコード
        ob_start();
        imagepng($imgFinal);
        $bin = ob_get_clean();

        // 後処理
        imagedestroy($imgLarge);
        imagedestroy($imgFinal);

        return base64_encode($bin);
    }

    // -----------------------------------------------
    // 以下、描画に関わる各種メソッド
    // スケールに合わせて引数で渡される座標・サイズを
    // すべて「拡大後の値」で扱う
    // -----------------------------------------------
    protected static function drawAccentShape($img, $shapeType, $cx, $cy, $size, $color)
    {
        switch ($shapeType) {
            case 0:
                // 丸
                imagefilledellipse($img, $cx, $cy, $size, $size, $color);
                break;
            case 1:
                // 角丸四角
                self::drawRoundedRect($img, $cx - $size / 2, $cy - $size / 2, $cx + $size / 2, $cy + $size / 2, $size * 0.3, $color);
                break;
            case 2:
                // 角丸三角
                self::drawRoundedTriangle($img, $cx, $cy, $size / 2, $color);
                break;
        }
    }

    protected static function drawFace($img, $cx, $cy, $r, $eyeShape, $mouthShape, $color)
    {
        // 目
        $eyeOffsetX = $r * 0.2;
        $eyeOffsetY = - ($r * 0.15);
        $eyeSize    = max(6, $r * 0.07);

        $lx = $cx - $eyeOffsetX;
        $rx = $cx + $eyeOffsetX;
        $y  = $cy + $eyeOffsetY;

        self::drawEye($img, $eyeShape, $lx, $y, $eyeSize, $color);
        self::drawEye($img, $eyeShape, $rx, $y, $eyeSize, $color);

        // 口
        $mouthW = $r * 0.25;
        $mouthH = $r * 0.10;
        $mouthY = $cy + $r * 0.10;

        self::drawMouth($img, $mouthShape, $cx, $mouthY, $mouthW, $mouthH, $color);
    }

    protected static function drawEye($img, $shape, $cx, $cy, $size, $color)
    {
        switch ($shape) {
            case 0:
                imagefilledellipse($img, $cx, $cy, $size, $size, $color);
                break;
            case 1:
                imagefilledrectangle($img, $cx - $size / 2, $cy - $size / 2, $cx + $size / 2, $cy + $size / 2, $color);
                break;
            case 2:
                imagefilledellipse($img, $cx, $cy, $size * 1.6, $size * 0.7, $color);
                break;
        }
    }

    protected static function drawMouth($img, $shape, $cx, $cy, $w, $h, $color)
    {
        switch ($shape) {
            case 0:
                imagefilledarc($img, $cx, $cy, $w, $h * 2, 10, 170, $color, IMG_ARC_PIE);
                break;
            case 1:
                imagesetthickness($img, 3);
                imageline($img, $cx - $w / 2, $cy, $cx + $w / 2, $cy, $color);
                break;
            case 2:
                imagefilledellipse($img, $cx, $cy, $w * 0.4, $h * 1.8, $color);
                break;
        }
    }

    protected static function drawRoundedRect($img, $left, $top, $right, $bottom, $radius, $color)
    {
        imagefilledrectangle($img, $left + $radius, $top, $right - $radius, $bottom, $color);
        imagefilledrectangle($img, $left, $top + $radius, $right, $bottom - $radius, $color);

        imagefilledarc($img, $left + $radius,  $top + $radius,    $radius * 2, $radius * 2, 180, 270, $color, IMG_ARC_PIE);
        imagefilledarc($img, $right - $radius, $top + $radius,    $radius * 2, $radius * 2, 270, 360, $color, IMG_ARC_PIE);
        imagefilledarc($img, $left + $radius,  $bottom - $radius, $radius * 2, $radius * 2, 90,  180,  $color, IMG_ARC_PIE);
        imagefilledarc($img, $right - $radius, $bottom - $radius, $radius * 2, $radius * 2, 0,   90,   $color, IMG_ARC_PIE);
    }

    protected static function drawRoundedTriangle($img, $cx, $cy, $r, $color)
    {
        $points = [];
        for ($i = 0; $i < 3; $i++) {
            $angle = deg2rad(120 * $i - 90);
            $x = $cx + $r * cos($angle);
            $y = $cy + $r * sin($angle);
            $points[] = [$x, $y];
        }
        $pp = [];
        foreach ($points as $p) {
            $pp[] = $p[0];
            $pp[] = $p[1];
        }
        imagefilledpolygon($img, $pp, 3, $color);

        $cornerR = $r * 0.3;
        foreach ($points as $p0) {
            imagefilledarc($img, $p0[0], $p0[1], $cornerR * 2, $cornerR * 2, 0, 360, $color, IMG_ARC_PIE);
        }
    }

    protected static function applyCircleMask($img, $cx, $cy, $r, $transparent, $size)
    {
        $r2 = $r * $r;
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $dx = $x - $cx;
                $dy = $y - $cy;
                if (($dx * $dx + $dy * $dy) > $r2) {
                    imagesetpixel($img, $x, $y, $transparent);
                }
            }
        }
    }

    // -----------------------------------------------
    // カラー作成 / HSL→RGB変換
    // -----------------------------------------------
    protected static function createColorFromHash($hash, $offset)
    {
        $h = hexdec(substr($hash, $offset, 3)) % 360;
        $s = 60;
        $l = 80;
        return self::hslToRgb($h, $s, $l);
    }

    protected static function hslToRgb($h, $s, $l)
    {
        $s /= 100.0;
        $l /= 100.0;
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        if ($h < 60) {
            $r = $c;
            $g = $x;
            $b = 0;
        } else if ($h < 120) {
            $r = $x;
            $g = $c;
            $b = 0;
        } else if ($h < 180) {
            $r = 0;
            $g = $c;
            $b = $x;
        } else if ($h < 240) {
            $r = 0;
            $g = $x;
            $b = $c;
        } else if ($h < 300) {
            $r = $x;
            $g = 0;
            $b = $c;
        } else {
            $r = $c;
            $g = 0;
            $b = $x;
        }

        $r = (int)(($r + $m) * 255);
        $g = (int)(($g + $m) * 255);
        $b = (int)(($b + $m) * 255);
        return [$r, $g, $b];
    }
}

