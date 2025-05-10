<?php
function scrapeUNSNews($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/91.0.4472.124 Safari/537.36');
    
    $html = curl_exec($ch);
    if ($html === false) {
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    
    $titles = $xpath->query('//h2[contains(@class, "elementor-post__title")]/a');
    // $titles = $xpath->query('//h3/a');
    $news = [];
    
    foreach ($titles as $title) {
        $news[] = [
            'title' => trim($title->nodeValue),
            'link' => $title->getAttribute('href')
        ];
    }
    return $news;
}

$default_date = date('Y-m-d');
$selected_date = isset($_POST['news_date']) ? $_POST['news_date'] : $default_date;

if (!DateTime::createFromFormat('Y-m-d', $selected_date)) {
    $selected_date = $default_date;
}

$today = new DateTime();
$input_date = new DateTime($selected_date);
if ($input_date > $today) {
    $selected_date = $default_date;
}

list($year, $month, $day) = explode('-', $selected_date);
$url_en = "https://uns.ac.id/en/$year/$month/$day";
// $url_en = "https://ugm.ac.id/id/$year/$month/$day";
$url_id = "https://uns.ac.id/id/$year/$month/$day";
// $url_id = "https://ugm.ac.id/id/$year/$month/$day";

$news_id = scrapeUNSNews($url_id);
$news_en = scrapeUNSNews($url_en);

function getHariIndonesia($date) {
    $hariInggris = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $hariIndonesia = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $hari = date('l', strtotime($date));
    return str_replace($hariInggris, $hariIndonesia, $hari);
}
function getBulanIndonesia($date) {
    $bulanInggris = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    $bulanIndonesia = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $bulan = date('F', strtotime($date));
    return str_replace($bulanInggris, $bulanIndonesia, $bulan);
}
$hari = getHariIndonesia($selected_date);
$tanggal = date('j', strtotime($selected_date));
$bulan = getBulanIndonesia($selected_date);
$tahun = date('Y', strtotime($selected_date));
$formatted = "*UNS HARI INI. {$hari}, {$tanggal} {$bulan} {$tahun}*\n\n";


if (!empty($news_id)) {
    $formatted .= "*BERITA ID*\n";
    $i = 1;
    foreach ($news_id as $item) {
        $formatted .= "*$i. " . $item['title'] . "*\n" . $item['link'] . "\n\n";
        $i++;
    }
}

if (!empty($news_en)) {
    $formatted .= "*BERITA EN*\n";
    $i = 1;
    foreach ($news_en as $item) {
        $formatted .= "*$i. " . $item['title'] . "*\n" . $item['link'] . "\n\n";
        $i++;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>UNS News Scraper</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: #f5f5f5;
        }
        .container {
            background: #fff;
            padding: 25px;
            max-width: 900px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #0066cc;
        }
        textarea {
            width: 100%;
            height: 400px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            margin-top: 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        .form-container {
            text-align: center;
            margin-bottom: 20px;
        }
        input[type="date"] {
            padding: 10px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            margin-left: 10px;
            background: #0066cc;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background: #004c99;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>UNS News Scraper</h1>
        <form method="post">
            <div class="form-container">
                <label for="news_date">Pilih Tanggal:</label>
                <input type="date" name="news_date" id="news_date" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $selected_date; ?>">
                <button type="submit">Ambil Berita</button>
                <button type="button" onclick="copyText()">Copy Text</button>
            </div>
        </form>
        <textarea id="newsText" readonly><?php echo htmlspecialchars($formatted); ?></textarea>
    </div>

    <script>
        function copyText() {
            const textarea = document.getElementById("newsText");
            textarea.select();
            textarea.setSelectionRange(0, 99999); // For mobile
            document.execCommand("copy");
            alert("Teks telah disalin!");
        }
    </script>
</body>
</html>
