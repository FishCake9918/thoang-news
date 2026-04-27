<?php
function tinhTong($n)
{
    $s = 0;
    for ($i = 1; $i <= $n; $i++) {
        $s += $i;
    }
    return $s;
}

function bangtinhTong($n)
{
    $bang = [];
    $tongTam = 0;

    for ($i = 1; $i <= $n; $i++) {
        $tongTam += $i;
        $bang[] = [$i, $tongTam];
    }

    return $bang;
}

function lietKePythagore($max)
{
    $boBa = [];

    for ($a = 1; $a < $max; $a++) {
        for ($b = $a; $b < $max; $b++) {
            for ($c = $b; $c < $max; $c++) {
                if (($a * $a + $b * $b) == ($c * $c)) {
                    $boBa[] = [$a, $b, $c];
                }
            }
        }
    }

    return $boBa;
}

function sinhMangNgauNhien($soLuong = 1000, $min = 1, $max = 98)
{
    $arr = [];
    for ($i = 0; $i < $soLuong; $i++) {
        $arr[] = rand($min, $max);
    }
    return $arr;
}

function tinhMean($arr)
{
    return array_sum($arr) / count($arr);
}

function tinhVariance($arr)
{
    $mean = tinhMean($arr);
    $tong = 0;

    foreach ($arr as $value) {
        $tong += pow($value - $mean, 2);
    }

    return $tong / count($arr);
}

function thongKeTanSo($arr)
{
    $khoang = [
        '1-9'   => 0,
        '10-19' => 0,
        '20-29' => 0,
        '30-39' => 0,
        '40-49' => 0,
        '50-59' => 0,
        '60-69' => 0,
        '70-79' => 0,
        '80-89' => 0,
        '90-98' => 0
    ];

    foreach ($arr as $num) {
        if ($num >= 1 && $num <= 9) {
            $khoang['1-9']++;
        } elseif ($num >= 10 && $num <= 19) {
            $khoang['10-19']++;
        } elseif ($num >= 20 && $num <= 29) {
            $khoang['20-29']++;
        } elseif ($num >= 30 && $num <= 39) {
            $khoang['30-39']++;
        } elseif ($num >= 40 && $num <= 49) {
            $khoang['40-49']++;
        } elseif ($num >= 50 && $num <= 59) {
            $khoang['50-59']++;
        } elseif ($num >= 60 && $num <= 69) {
            $khoang['60-69']++;
        } elseif ($num >= 70 && $num <= 79) {
            $khoang['70-79']++;
        } elseif ($num >= 80 && $num <= 89) {
            $khoang['80-89']++;
        } elseif ($num >= 90 && $num <= 98) {
            $khoang['90-98']++;
        }
    }

    return $khoang;
}

$n = 100;
$tongS = tinhTong($n);
$bangTong = bangtinhTong($n);
$pythagore = lietKePythagore(20);
$mangNgauNhien = sinhMangNgauNhien(1000, 1, 98);
$tanSo = thongKeTanSo($mangNgauNhien);
$mean = tinhMean($mangNgauNhien);
$variance = tinhVariance($mangNgauNhien);
$maxTanSo = max($tanSo);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lab 05 - PHP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #ffffff;
            color: #000000;
        }

        h1, h2 {
            color: #000000;
        }

        .box {
            background: #f5f5f5;
            border: 1px solid #cccccc;
            padding: 20px;
            margin-bottom: 20px;
        }

        .numbers {
            max-height: 220px;
            overflow-y: auto;
            background: #ffffff;
            padding: 10px;
            border: 1px solid #cccccc;
            line-height: 1.8;
        }

        .bang-div {
            width: 100%;
            margin-top: 15px;
            border: 1px solid #999999;
        }

        .hang {
            display: flex;
        }

        .hang:not(:last-child) {
            border-bottom: 1px solid #cccccc;
        }

        .o {
            flex: 1;
            padding: 10px;
            text-align: center;
            border-right: 1px solid #cccccc;
        }

        .o:last-child {
            border-right: none;
        }

        .hang-tieu-de {
            background: #dddddd;
            font-weight: bold;
        }

        .hang-td {
            background: #ffffff;
        }

        .hang-tong {
            background: #eeeeee;
            font-weight: bold;
        }

        .chart {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            height: 320px;
            padding: 15px;
            border: 1px solid #cccccc;
            background: #ffffff;
            margin-top: 20px;
        }

        .bar-wrap {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            height: 100%;
        }

        .bar-value {
            margin-bottom: 5px;
            font-weight: bold;
        }

        .bar {
            width: 40px;
            background: #2f80ed;
        }

        .bar-label {
            margin-top: 8px;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>

<?php
echo "<h1>Lab 05 - Các bài toán với PHP</h1>";

echo "<div class='box'>";
echo "<h2>1. Tính tổng S = 1 + 2 + ... + n</h2>";
echo "<div class='bang-div'>";

echo "
    <div class='hang hang-tieu-de'>
        <div class='o'>Bước</div>
        <div class='o'>Giá trị cộng thêm</div>
        <div class='o'>Tổng tạm thời</div>
    </div>
";

foreach ($bangTong as $index => $dong) {
    echo "
        <div class='hang hang-td'>
            <div class='o'>" . ($index + 1) . "</div>
            <div class='o'>" . $dong[0] . "</div>
            <div class='o'>" . $dong[1] . "</div>
        </div>
    ";
}

echo "
    <div class='hang hang-tong'>
        <div class='o'>Tổng cuối cùng</div>
        <div class='o'></div>
        <div class='o'>" . $tongS . "</div>
    </div>
";

echo "</div>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>2. Liệt kê các bộ ba số Pythagore nhỏ hơn 20</h2>";
echo "<div class='bang-div'>";

echo "
    <div class='hang hang-tieu-de'>
        <div class='o'>STT</div>
        <div class='o'>a</div>
        <div class='o'>b</div>
        <div class='o'>c</div>
    </div>
";

foreach ($pythagore as $index => $bo) {
    echo "
        <div class='hang hang-td'>
            <div class='o'>" . ($index + 1) . "</div>
            <div class='o'>" . $bo[0] . "</div>
            <div class='o'>" . $bo[1] . "</div>
            <div class='o'>" . $bo[2] . "</div>
        </div>
    ";
}

echo "</div>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>3. Sinh ngẫu nhiên 1000 số nhỏ hơn 99</h2>";
echo "<div class='numbers'>";

foreach ($mangNgauNhien as $i => $value) {
    echo $value;
    if ($i < count($mangNgauNhien) - 1) {
        echo ", ";
    }
}

echo "</div>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>4. Thống kê tần số theo khoảng</h2>";
echo "<div class='bang-div'>";

echo "
    <div class='hang hang-tieu-de'>
        <div class='o'>Khoảng</div>
        <div class='o'>Tần số</div>
    </div>
";

foreach ($tanSo as $k => $v) {
    echo "
        <div class='hang hang-td'>
            <div class='o'>" . $k . "</div>
            <div class='o'>" . $v . "</div>
        </div>
    ";
}

echo "</div>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>5. Mean và Variance</h2>";
echo "<p><strong>Mean:</strong> " . round($mean, 2) . "</p>";
echo "<p><strong>Variance:</strong> " . round($variance, 2) . "</p>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>6. Biểu đồ tần số</h2>";
echo "<div class='chart'>";

foreach ($tanSo as $k => $v) {
    $height = ($v / $maxTanSo) * 250;

    echo "
        <div class='bar-wrap'>
            <div class='bar-value'>" . $v . "</div>
            <div class='bar' style='height: " . $height . "px;'></div>
            <div class='bar-label'>" . $k . "</div>
        </div>
    ";
}

echo "</div>";
echo "</div>";
?>

</body>
</html>