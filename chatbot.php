<?php
session_start();
include("db.php");

$api_key = "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"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prompt = trim($_POST['prompt'] ?? '');
    $property_info = "";
    $is_property_question = false;

    if (!empty($prompt)) {
       
        $keywords = ['property', 'flat', 'house', 'apartment', 'room', 'bhk', 'rent', 'available', 'location', 'bedroom'];
        foreach ($keywords as $word) {
            if (stripos($prompt, $word) !== false) {
                $is_property_question = true;
                break;
            }
        }
        if ($is_property_question) {
            $query = "SELECT id, title, description, location, price, type, bedrooms, bathrooms, area, photo_paths 
                      FROM properties 
                      WHERE approved = 1 AND availability_status = 'available'";
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                $property_info = "<div class='row'>";
                while ($row = mysqli_fetch_assoc($result)) {
                    $photos = explode(',', $row['photo_paths']);
                    $photo = 'http://localhost/realestate/' . trim($photos[0] ?? 'assets/default.jpg');
                    $detail_url = "property_details.php?id=" . $row['id'];

                    $property_info .= <<<HTML
<div class="col-md-4 mb-4">
    <div class="card shadow-sm" style="border-radius: 10px;">
        <img src="{$photo}" class="card-img-top" alt="Property" style="height:220px; object-fit:cover;">
        <div class="card-body">
            <h5 class="card-title">{$row['title']}</h5>
            <p class="card-text">{$row['location']} | {$row['type']}</p>
            <p class="card-text">Rs. {$row['price']} | {$row['area']} sq ft</p>
            <p class="card-text">{$row['bedrooms']} Beds | {$row['bathrooms']} Baths</p>
            <a href="{$detail_url}" class="btn btn-outline-primary w-100 mb-2">View Details</a>
        </div>
    </div>
</div>
HTML;
                }
                $property_info .= "</div>";
            } else {
                $property_info = "<div class='alert alert-info'>Sorry, no properties found.</div>";
            }
        }

        // Prepare GPT messages
        $messages = [];

        if ($is_property_question) {
            $messages[] = [
                "role" => "system",
                "content" => "You are a helpful real estate assistant. Show all available properties as Bootstrap 5 cards with image, title, price, and a 'View Details' button. Here's the data:\n\n" . $property_info
            ];
        } else {
            $messages[] = [
                "role" => "system",
                "content" => "You are a helpful assistant on a real estate site. Answer any general question in HTML."
            ];
        }

        $messages[] = [
            "role" => "user",
            "content" => $prompt
        ];

        // Call OpenRouter API
        $data = [
            "model" => "openai/gpt-3.5-turbo",
            "messages" => $messages
        ];

        $ch = curl_init("https://openrouter.ai/api/v1/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $api_key"
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $_SESSION['chat_response'] = "<div class='alert alert-danger'>cURL error: " . curl_error($ch) . "</div>";
        } else {
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $response_data = json_decode($response, true);

            if ($http_status !== 200) {
                $_SESSION['chat_response'] = "<div class='alert alert-warning'>Error $http_status: " . ($response_data['error']['message'] ?? 'Unknown error') . "</div>";
            } else {
                $_SESSION['chat_response'] = $response_data['choices'][0]['message']['content'] ?? "<div class='alert alert-secondary'>No response received.</div>";
            }
        }

        curl_close($ch);
    } else {
        $_SESSION['chat_response'] = "<div class='alert alert-info'>Prompt cannot be empty.</div>";
    }
}

header("Location: buyer_dashboard.php");
exit();
