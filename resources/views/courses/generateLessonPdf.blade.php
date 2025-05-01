<!DOCTYPE html>
<html>
<head>
    <title>Sub Lesson PDF</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 20px;">

    <!-- Course Name -->
    <h2 style="text-align: center; margin-bottom: 10px;">{{ $sublesson_detail[0]['course']['course_name'] ?? '' }}</h2>

    <!-- Sub Lesson Details -->
    <div style="margin-bottom: 30px; padding: 15px; border: 1px solid #ccc; background-color: #f9f9f9;">
        <h4 style="margin-bottom: 10px;">Sub Lesson Details</h4>
        <p><strong>Title:</strong> {{ $sublesson_detail[0]->lesson_title ?? '' }}</p>
        <p><strong>Description:</strong> {{ $sublesson_detail[0]->description ?? '' }}</p>
      
    </div>



</body>
</html>
