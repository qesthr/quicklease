<?php
require_once '../db.php'; // Ensure this initializes $pdo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $model = trim($_POST["model"]);
    $plate_no = trim($_POST["plate_no"]);
    $price = trim($_POST["price"]);
    $status = $_POST["status"];
    $image_name = "";

    // Validate input
    if (empty($model) || empty($plate_no) || empty($price) || empty($status)) {
        $error = "All fields are required!";
    } else {
        // Handle file upload
        if (!empty($_FILES["car_image"]["name"])) {
            $image_name = uniqid() . "_" . basename($_FILES["car_image"]["name"]);
            $target_dir = "../uploads/";
            $target_file = $target_dir . $image_name;

            // Check if the uploads directory exists
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true); // Create the directory if it doesn't exist
            }

            // Check file type (only allow images)
            $allowed_types = ["image/jpeg", "image/png", "image/gif"];
            if (!in_array($_FILES["car_image"]["type"], $allowed_types)) {
                $error = "Invalid file type! Please upload JPEG, PNG, or GIF.";
            } elseif ($_FILES["car_image"]["size"] > 5000000) { // Limit: 5MB
                $error = "File size too large! Max 5MB.";
            } else {
                if (!move_uploaded_file($_FILES["car_image"]["tmp_name"], $target_file)) {
                    $error = "Error uploading file!";
                }
            }
        }

        if (empty($error)) {
            // Insert into database using PDO
            $stmt = $pdo->prepare("INSERT INTO car (model, plate_no, price, status, image) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$model, $plate_no, $price, $status, $image_name])) {
                header("Location: cars.php");
                exit();
            } else {
                $error = "Error adding car.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Car</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; text-align: center; }
        form { width: 50%; margin: auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        input, select { width: 100%; padding: 10px; margin-bottom: 10px; }
        .btn { background: #ffb400; padding: 10px 15px; border: none; cursor: pointer; color: black; }
        .error { color: red; }
    </style>
</head>
<body>

<h2>Add a New Car</h2>

<?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

<form method="post" enctype="multipart/form-data">
    <label>Car Model:</label>
    <input type="text" name="model" required>
    
    <label>Plate No:</label>
    <input type="text" name="plate_no" required>
    
    <label>Price Per Day:</label>
    <input type="number" name="price" step="0.01" required>
    
    <label>Status:</label>
    <select name="status">
        <option value="Available">Available</option>
        <option value="Rented">Rented</option>
        <option value="Maintenance">Maintenance</option>
    </select>
    
    <label>Upload Car Image:</label>
    <input type="file" name="car_image" accept="image/*" required>

    <button type="submit" class="btn">Add Car</button>
</form>

</body>
</html>