<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !validateEmail($email)) {
        setMessage('error', 'Please enter a valid email address.');
    } else {
        $conn = getDBConnection();
        $check = $conn->prepare("SELECT id FROM subscribers WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            setMessage('warning', 'This email is already subscribed.');
        } else {
            $stmt = $conn->prepare("INSERT INTO subscribers (email) VALUES (?)");
            $stmt->bind_param("s", $email);
            if ($stmt->execute()) {
                setMessage('success', 'Thank you! You have been subscribed successfully.');
            } else {
                setMessage('error', 'Something went wrong. Please try again.');
            }
        }
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit();
?>