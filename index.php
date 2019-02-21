<link rel="stylesheet" href="main.css">

<script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>


<?php
$db = new SQLite3('/home/joe/transit/transit.db');
$results = $db->query('SELECT line,arrivaltime from times');
$rows = array();
while ($row = $results->fetchArray()) {
        $rows['object_name'][] = $row;
}
json_encode($rows);
?>
<body onload="Javascript:timedRefresh(300000);">
<div class="container">
<table id="timetable">
</table>
<data></data>
</div>
</body>
<script type="text/javascript">
/* Create a function called refreshTable() and place all of the table update actions inside */
function refreshTable() {
        var time = new Date();
        var times = <?php echo json_encode($rows) ?>;

        /* Clear the contents of the table, otherwise we will start stacking duplicate tables
                * ontop of one another every 5 seconds */
        $('#timetable').empty()
        for (var i=0, row; row = times.object_name[i]; i++) {
                try {
                        /* Declare variables */
                        var timetext = row['arrivaltime'];
                        var pieces = timetext.split(/[: ]+/);
                        var hour, minute, ampm;
                        var linepieces = row['line'].split(/ (.*)/);
                        var hourdiff, mindiff, ttldiff;

                        /* Make the hours and minutes into integers, from text */
                        if (pieces.length == 3) {
                                hour = parseInt(pieces[0],10);
                                minute = parseInt(pieces[1],10);
                                ampm = pieces[2];
                        }

                        /* Time from sql is in AM/PM format - need to convert to 24h time */
                        if (ampm == 'PM') {
                                /* If it's 12:00PM, that should be 12 (not 24), otherwise if
                                 * it's PM, add 12 */
                                hour == 12 ? hour = 12 : hour += 12;
                        }

                        /*Get bus time difference vs. current system time & convert to mins */
                        hourdiff = hour - time.getHours();
                        mindiff = minute - time.getMinutes();
                	ttldiff = hourdiff*60 + mindiff;
	
			/*Add '  mins' suffix if >1, ' min ' if 1 min
                        Note the below is just if/else shorthand in JS */
                        ttldiff == 1 ? ttldifftext = ttldiff + ' min ' : ttldifftext = ttldiff + ' mins';

                        /* If the bus hasn't already left, then print it as a row in the table */
                        if (ttldiff > 0) {
                                $("#timetable").append($('<tr>')
                                        .append($('<td>').text(linepieces[0]))
                                        .append($('<td>').text(linepieces[1]))
                                        .append($('<td>').text(ttldifftext))
                                );
                        }
                }
                catch(err) {
                        var err;
                }
}
}
/* Refresh the table immediately upon page refresh */
refreshTable();

/* Refresh the table every 5 seconds thereafter */
window.setInterval(refreshTable,5000);

/* Lastly, we need PHP to re-query the database , which we'll do via this function to
        * re-load the web page */
function timedRefresh(timeoutPeriod) {
        setTimeout("location.reload(true);",timeoutPeriod);
}
</script>
