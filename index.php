<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Score calculator</title>
    <link rel="stylesheet" href="bulma.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php 
        $courseErr = $teacherErr = "";
        $course = $teacher = "";
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (empty($_POST["course"])) {
                $courseErr = "Course name is required";
            } else {
                $course = test_input($_POST["course"]);
                if (!preg_match("/^[a-zA-Z0-9 ]*$/",$course)) {
                    $courseErr = "Invalid course name format"; 
                }
            }
            if (empty($_POST["teacher"])) {
                $teacherErr = "Teacher name is required";
            } else {
                $teacher = test_input($_POST["teacher"]);
                if (!preg_match("/^[a-zA-Z ]*$/",$teacher)) {
                    $teacherErr = "Invalid teacher name format"; 
                }
            }
            
        } 
        
        function test_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        function cal($data, $dattaSize, $text) {
            $sum = $min = $max = $avg = $x = $count = 0;
            for ($x = 0; $x < $dattaSize; $x++) {
                if ($data[$x] != ""){
                    $sub = explode(",", $data[$x]);
                    if ($x == 1){
                        $min = $sub[1];
                        $max = $sub[1];
                    }
                    $tmp = $sub[1];
                    $sum += $tmp;
                    if ($tmp < $min){
                        $min = $tmp;
                    }
                    if ($tmp > $max){
                        $max = $tmp;
                    }
                    $count++;
                }
                else{
                    $dattaSize--;
                }

            }
            $avg = $sum/$count;
            $text = $text."Students : ".$count."<br>"."Sum Score : ".$sum."<br>"."Max Score : ".$max."<br>"."Min Score : ".$min."<br>"."Average Score : ".$avg."<br>";
            return $text;
        }
    ?>

    <section class="section hero is-fullheight is-fullwidth">
        <div class="container">
             <div class="level-item">
                <div class="field is-horizon">
                    <div class="field">
                        <h1 class="title is-1">Student score calculator</h1>
                    </div>

                    <div class="field">
                        <h5 class="subtitle is-6">* is require</h1>
                    </div>

                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" enctype="multipart/form-data">
                        <div class="field">
                            <label class="label">Course name</label>
                            <div class="control">
                                <input class="input" type="text" placeholder="Text input" name="course">
                                <span class="error">*<?php echo $courseErr?> </span>
                            </div>
                            <p class="help">Course name contain only letters, numbers and white space</p>
                        </div>
                            
                        <div class="field">
                            <label class="label">Teacher name</label>
                            <div class="control">
                                <input class="input" type="text" placeholder="Text input" name="teacher">
                                <span class="error">*<?php echo $teacherErr?> </span>
                            </div>
                            <p class="help">Teacher name contain only letters and white space</p>
                        </div> 

                        <div class="field">
                            <label class="label">Download form</label>
                            <a class="button" href="Form.csv" >Download students score form</a>
                        </div>

                        <div class="field">
                            <label class="label">Upload form</label>
                            <div class="file has-name is-left">
                                <label class="file-label" >
                                    <input class="file-input" type="file" name="fileToUpload" onchange=getFileData(this) >
                                    <span class="file-cta">
                                        <span class="file-label" >
                                            Choose a file
                                        </span>
                                    </span>
                                    <span class="file-name" id='select' >
                                        ...
                                    </span>
                                </label>
                            </div>
                        </div>

                        <?php
                            if (isset($_POST["submit"])){
                                $target_dir = "";
                                $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
                                $uploadOk = 1;
                                $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
                                if(empty($target_file)) {
                                    echo "Please select CSV files.";
                                    $uploadOk = 0;
                                }
                                elseif ($fileType != "csv" && !empty($target_file) ) {
                                    echo "Sorry, only CSV files are allowed.";
                                    $uploadOk = 0;
                                }
                                elseif ($uploadOk == 0) {
                                    echo "Sorry, your file was not uploaded.";
                                }
                                elseif (!empty($course) && !empty($teacher)) {
                                    move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file) or die("Sorry, there was an error uploading your file.");
                                }
                            }
                        ?>
                        <div class="field">
                            <input class="button" type="submit" name="submit" value="Submit">
                        </div>
                    </form>

                    <div class="box">
                        <?php
                            if(!empty($_FILES["fileToUpload"]["name"]) && !empty($course) && !empty($teacher)){
                                $myfile = fopen(basename($_FILES["fileToUpload"]["name"]), "r") or die("Unable to open file!");
                                $input = fread($myfile,filesize($_FILES["fileToUpload"]["name"]));
                                $input = str_replace("No.,Score", "", $input);
                                if (!empty($input)){         
                                    $array = preg_split("/[\s]+/", $input);
                                    $arraySize = count($array);
                                    $text = "Student score in ".$course." by ".$teacher."."."<br><br>";
                                    $text = cal($array, $arraySize, $text);
                                    echo $text;
                                }
                                else{
                                    echo "No data in selected file";
                                }
                                fclose($myfile);

                                if (basename($_FILES["fileToUpload"]["name"] == "Form.csv")){
                                    $myfile = fopen(basename($_FILES["fileToUpload"]["name"]), "w") or die("Unable to open file!");
                                    $txt = "No.,Score";
                                    fwrite($myfile, $txt);
                                    fclose($myfile);
                                }
                            }
                        ?>
                    </div>
                </div>
            </div> 
        </div>
    </section>
    <script>
        function getFileData(object){
            var file = object.files[0];
            var name = file.name;
            document.getElementById('select').innerHTML=name;
        }
    </script>
</body>
</html>