<!DOCTYPE html>
<html>

<head>
    <style>
        /* Your CSS styling here */
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
        }

        .content {
            margin: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>BARANGAY CLEARANCE CERTIFICATE</h1>
    </div>
    <div class="content">
        <p>This certifies that <strong>{{ $name }}</strong>...</p>
        <!-- More content with {{ $variable }} placeholders -->
    </div>
</body>

</html>
