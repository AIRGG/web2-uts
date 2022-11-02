<?php
if (isset($_FILES['excelnya'])) {
    $errors = array();
    $file_name = $_FILES['excelnya']['name']; // ambil file yg diupload
    $file_size = $_FILES['excelnya']['size']; // ambil file yg diupload
    $file_tmp = $_FILES['excelnya']['tmp_name']; // ambil file yg diupload
    $file_type = $_FILES['excelnya']['type']; // ambil file yg diupload
    $tmp = explode('.', $file_name); // pisahin nama sama extensi filenya
    $file_ext = strtolower(end($tmp)); // ambil extensi filenya
    // $file_ext = strtolower(end(explode('.', $_FILES['excelnya']['name'])));

    $extensions = array("xlsx", "xls");

    if (in_array($file_ext, $extensions) === false) { // jika extensi filenya bukan excel, TOLAK!
        $errors[] = "extension not allowed, please choose a Excel file.";
    }

    // if($file_size > 2097152) {
    //    $errors[]='File size must be excately 2 MB';
    // }

    if (empty($errors) == true) {
        move_uploaded_file($file_tmp, "myexcel.xlsx");
        echo "Success";
    } else {
        print_r($errors);
    }
}
?>
<br><br>
<a href="admin.php">Kembali</a>