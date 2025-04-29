<?php

/**
 * 检测用户代理字符串判断是否为移动设备
 * 使用正则匹配常见移动设备关键词
 * @return bool 返回是否为移动设备
 */
function isMobileDevice()
{
    // 在使用 HTTP_USER_AGENT 之前检查它是否已设置。
    if (isset($_SERVER["HTTP_USER_AGENT"])) {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }
    return false; // 如果未设置，则假定为非移动设备（或根据你的需求调整）。
}

// 基础图片存储路径（注意：实际路径可能需要调整）
$baseImagePath = 'https://img.mod.wiki/images/';
$pcImageFolder = 'images_pc';    // PC端图片目录
$mobileImageFolder = 'images_mobile'; // 移动端图片目录

/**
 * 获取指定目录下的随机webp文件
 * @param string $folderPath 要扫描的目录路径
 * @return string|null 随机文件路径或null（无文件时）
 */
function getRandomWebpFile($folderPath)
{
    // 使用 glob 函数获取目录下所有 .webp 文件
    // glob 返回一个包含匹配文件路径的数组
    $files = glob($folderPath . '/*.webp');
    // 如果目录为空，返回 null；否则随机选择一个文件
    // array_rand 函数从数组中随机选择一个键名
    return empty($files) ? null : $files[array_rand($files)];
}

// 根据设备类型选择图片目录
// 如果 isMobileDevice() 返回 true，选择移动端目录；否则选择 PC 端目录
$imageFolderPath = isMobileDevice()
    ? $baseImagePath . $mobileImageFolder
    : $baseImagePath . $pcImageFolder;

// 获取随机图片路径
$imagePath = getRandomWebpFile($imageFolderPath);

// 处理API请求（当URL中包含?api参数时）
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['api'])) {
    // 如果没有找到图片，返回404错误
    if (!$imagePath) {
        http_response_code(404); // 设置 HTTP 状态码为 404
        header('Content-Type: application/json'); // 设置 JSON 内容类型
        echo json_encode(['error' => 'No images found.']); // 返回 JSON 格式的错误信息
    } else {
        // 使用真实的相对路径（基于 Web 根目录，而不是脚本位置）
        $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $imagePath);
        // 或者,如果你的图片不在document_root下面，计算相对于域名的路径：
        //$relativePath = str_replace('E:\\project\\pic_api', '', $imagePath); // 替换为你的图片目录的绝对路径
        $relativePath = str_replace('\\', '/', $relativePath); // 统一使用正斜杠

        header('Content-Type: application/json');
        echo json_encode(['image' => $relativePath]);
    }
    exit; // 结束执行，不输出 HTML
}

// 常规请求处理（无API参数时）
if (!$imagePath) {
    // 如果没有找到图片，返回404错误
    http_response_code(404);
    echo "No images found.";
    exit;
}

// 如果是命令行运行，不输出 HTML。  这可以防止你在测试时不小心看到 HTML。
if (PHP_SAPI === 'cli') {
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>元素云API</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-image: url('<?php
                $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $imagePath);
                //或者，如果你的图片不在document_root下面，计算相对于域名的路径：
                // $relativePath = str_replace('E:\\project\\pic_api', '', $imagePath); // 替换为你的图片目录的绝对路径
                $relativePath = str_replace('\\', '/', $relativePath);
                echo htmlspecialchars($relativePath);
            ?>');
            background-size: cover;
            background-position: center;
            overflow: hidden;
            color: #fff;
        }
        .logo-container {
            text-align: center;
            padding: 20px;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        .content-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            flex: 1;
            padding: 20px;
        }
        .gallery-container {
            display: flex;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .gallery-container::-webkit-scrollbar {
            display: none;
        }
        .container {
            position: relative;
            width: 90%;
            max-width: 30%;
            text-align: center;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            margin-right: 20px;
        }
        @media (max-width: 768px) {
            body {
                margin: 0;
            }
            .gallery-container {
                flex-direction: row;
                gap: 20px;
                align-items: flex-start;
            }
            .container {
                max-width: 100%;
                margin-right: 0;
            }
            .nav-button {
                top: auto;
                bottom: 10px;
                z-index: 10;
            }
            .prev-button {
                left: 10px;
            }
            .next-button {
                right: 10px;
            }
        }
        @media (min-width: 769px) {
            .container {
                max-width: 30%;
            }
        }
        .text-header {
            font-size: 24px;
            color: #fff;
            margin-bottom: 20px;
        }
        .image-container {
            margin: 0 auto;
            border: none;
            padding: 0;
            width: 100%;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .image-container:hover {
            transform: scale(1.05);
        }
        .image-container img {
            max-width: 100%;
            height: auto;
            display: block;
            border-radius: 10px;
        }
        .api-url {
            margin-top: 20px;
            font-size: 16px;
            color: #ddd;
        }
        .api-url a {
            color: #aaa;
            text-decoration: none;
        }
        .api-url a:hover {
            text-decoration: underline;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.7);
            text-align: center;
            font-size: 14px;
            color: #ccc;
            padding: 10px 0;
        }
        .nav-button {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: absolute;
            color: #fff;
            font-size: 24px;
        }
        .prev-button {
            left: 10px;
        }
        .next-button {
            right: 10px;
        }
    </style>
</head>
<body>
<div class="logo-container">
    <img src="logo.png" alt="Logo" class="logo">
</div>
<div class="content-container">
    <div class="gallery-container">
        <button class="nav-button prev-button" onclick="scrollGallery(-300)">❮</button>
        <div class="container">
            <h1 class="text-header">自适应二次元</h1>
            <div class="image-container" id="acgImageContainer">
                <img src="<?php
                $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $imagePath);
                //或者，如果你的图片不在document_root下面，计算相对于域名的路径：
                //$relativePath = str_replace('E:\\project\\pic_api', '', $imagePath); // 替换为你的图片目录的绝对路径
                $relativePath = str_replace('\\', '/', $relativePath);
                echo htmlspecialchars($relativePath);
                ?>" alt="Random Image" id="randomACGImage">
            </div>
            <div class="api-url">GET: <a href="https://img.mod.wiki/acg/">https://img.mod.wiki/acg/</a></div>
        </div>
        <!--  Bing每日一图  -->
        <div class="container">
            <h1 class="text-header">Bing每日一图自适应</h1>
            <div class="image-container" id="bingImageContainer">
                <img src="" alt="Bing Daily Image" id="randomBingImage">
            </div>
            <div class="api-url">API: <a href="https://img.mod.wiki/bing/api.php" target="_blank">https://img.mod.wiki/bing/api.php</a></div>
        </div>

        <!--  Wgzdy定制随机图  -->
        <div class="container">
            <h1 class="text-header">Wgzdy定制随机图</h1>
            <div class="image-container" id="WgzdyImageContainer">
                <img src="" alt="WgzdyImage" id="randomWgzdyImage">
            </div>

            <div class="api-url">API: <a href="https://img.mod.wiki/wgzdy/" target="_blank">https://img.mod.wiki/wgzdy/</a></div>
        </div>

        <button class="nav-button next-button" onclick="scrollGallery(300)">❯</button>
    </div>
</div>
<div class="footer">
    &copy; 2025 广东元素周期表. All rights reserved.<br>
    粤ICP备2022047371号-2
</div>

<script>
    async function fetchRandomACGImage() {
        try {
            const response = await fetch('?api=true&t=' + Date.now());
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            if (data.image) {
                document.getElementById('randomACGImage').src = data.image;
            } else {
                console.error('API returned an empty or invalid image path.');
            }
        } catch (error) {
            console.error('There was a problem with the fetch operation:', error);
        }
    }
    async function fetchRandomBingImage() {
        try {
            const response = await fetch('https://img.mod.wiki/bing/api.php');  // 确保这里的 URL 是正确的
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            document.getElementById('randomBingImage').src = response.url; // 设置图片路径,这里返回的是重定向后的URL，不需要解析JSON。
        } catch (error) {
            console.error('There was a problem with the fetch operation:', error);
        }
    }
    async function fetchRandomWgzdyImage() {
        try {
            const response = await fetch('https://img.mod.wiki/wgzdy/');
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            document.getElementById('randomWgzdyImage').src = response.url; // 设置图片路径
        } catch (error) {
            console.error('There was a problem with the fetch operation:', error);
        }
    }

    fetchRandomACGImage();
    fetchRandomBingImage();
    fetchRandomWgzdyImage();

    document.getElementById('acgImageContainer').addEventListener('click', fetchRandomACGImage);
    document.getElementById('bingImageContainer').addEventListener('click', fetchRandomBingImage);
    document.getElementById('WgzdyImageContainer').addEventListener('click', fetchRandomWgzdyImage);

    function scrollGallery(scrollAmount) {
        document.querySelector('.gallery-container').scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const galleryContainer = document.querySelector('.gallery-container');
        galleryContainer.style.touchAction = 'pan-x';
    });
</script>
</body>
</html>