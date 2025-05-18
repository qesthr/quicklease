<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/includes/db.php';



    $mpdf = new \Mpdf\Mpdf();
    header('Content-Type: application/pdf');

    $stmt = $pdo->query("SELECT * FROM product");
    // $products = $stmt->fetchAll(PDO::ASSOC);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = 1;

    $html = '
        <html>
            <head>
                <style>
                    body {
                        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                        font-size: 12px;
                        padding: 20px;
                        color: #333;
                        }

                    h4 {
                        text-align: center;
                        margin-bottom: 20px;
                        }
                    
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 11px;
                        }
                    
                    td {
                        background-color: #f8f9fa;
                        border: 1px solid #ccc;
                        padding: 8px;
                        text-align: left;
                    }
                    .signature-section {
                        margin-top: 40px;
                        display: flex;
                        justify-content: space-between;
                        font-size: 11px;
                    }
                    .signature {
                    width: 50%;
                    text-align: center;
                    
                    }
                </style>            
            </head>
            <body>
                <h4>Product List</h4>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                <tbody>';

                foreach ($products as $product) {
                    $html .= '
                            <tr>
                                <td> ' . $count++ . '</td>
                                <td> ' . htmlspecialchars($product['product_name']) . '</td>
                                <td> ' . htmlspecialchars($product['category']) . '</td>
                                <td> ' . htmlspecialchars($product['price']) . '</td>
                                <td> ' . htmlspecialchars($product['stock']) . '</td>
                            </tr>';
                }

                $html .= '
                </tbody>
                </table>

                <div class="signature-section">
                    <div class="signature">
                        <p>_________________________________________</p>
                        <p><strong>General Manager</strong></p>
                    </div>
                </div>
            </body>
        </html>';

        $mpdf->SetHTMLFooter('
                <div style="text-align: left;">
                    Page {PAGENO}/{nbpg}
                </div>');

        $mpdf->WriteHTML($html);
        $mpdf->Output('','I');
        exit;
    
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Products</title>

    <link rel="icon" type="img/png" href="http://localhost/ipt/images/joeni.jpg">
    <link rel="stylesheet" href="http://localhost/ipt/bootstrap-5.3.3-dist/css/bootstrap.min.css">

</head>
<body>
    <form action="" method="POST">
        <button type="submit" class="btn btn-primary">
            Print Products
        </button>
    </form>
</body>
</html>