

<!DOCTYPE html>
<html>
<head>
    <title>Chilren's Account</title>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
</head>
<body>
     <?php include ("header.php"); ?>

            
<main class="container">
    <section class="child-card card">
      <h2>Child’s card</h2>
      <div class="child-info">
        <div class="avatar"></div>
        <h3>Olivia</h3>
        <p>10 y.o.<br/>IC Level 3A</p>
        <button>Go To Schedule</button>
      </div>
    </section>

    <section class="calendar card">
      <h2>CALENDAR</h2>
        <p id="month-label"></p>
        <div id="days" class="calendar-grid"></div>
    </section>

    <section class="training card">
      <h3>Training Schedule</h3>
      <p>Sun: 3:00 pm<br>Mon: 6:00 pm<br>Tue: 4:00 pm<br>Wed: 6:00 pm<br>Thu: 6:00 pm<br>Fri: 4:00 pm</p>
    </section>

    <section class="payments card">
      <h3>Payments</h3>
      <p>Recent Payment: <span class="amount">$450</span></p>
      <p>Upcoming: <span>May 1, 2025</span></p>
      <button>Add Payment</button>
    </section>

    <section class="achievements card">
      <h3>Achievements</h3>
      <div class="medals">
        <span>🥉</span>
        <span class="placeholder"></span>
        <span class="placeholder"></span>
      </div>
    </section>
  </main>
             <?php include ("footer.php"); ?>
</body>
