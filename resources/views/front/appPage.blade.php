<!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $content->title;?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
   <style>
            body{

                font-family: 'Nunito', sans-serif;
                background-color: #f1f1f1;
            }

        
        </style>
</head>
<body>

    <div class="container-fluid">
        <?php echo $content->content;?>
    </div>
  
</body>

</html>
