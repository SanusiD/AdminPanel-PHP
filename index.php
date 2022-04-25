<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="./css/main.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

</head>
<body>
    <?php
        require_once "dataConfig.php";
        // Attempt select query execution
        $salesTotalOrdersUsers = "SELECT SUM(grand_total) as total, COUNT(*) totalOrders,COUNT(DISTINCT cust_fname, cust_city,cust_province) AS totalUsers FROM salesdata";
        $tax = "SELECT SUM(tax) FROM salesdata";
        $data = "SELECT * FROM salesdata ORDER BY purchase_date DESC LIMIT 10";
        $shipping = "SELECT SUM(shipping) FROM salesdata";
        $totalUsers = "SELECT DISTINCT cust_fname, cust_city,cust_province from salesdata";
        $provinces = "SELECT cust_province, SUM(grand_total) as total_sales FROM salesdata  GROUP BY cust_province";
        $topcustomers = "SELECT cust_fname, SUM(grand_total) as total_sales FROM salesdata GROUP BY cust_fname ORDER BY total_sales DESC LIMIT 5";
        $yearlySales = "SELECT YEAR(purchase_date) year, SUM(grand_total) as total_sales,SUM(shipping) as shipping, SUM(tax) as tax FROM salesdata GROUP BY year ORDER BY year";
        if (isset($_POST['year'])) {
            $filter = $_POST['year'];
            $yearFilter = "SELECT MONTH(purchase_date) month, SUM(grand_total) as total_sales,SUM(shipping) as shipping, SUM(tax) as tax FROM salesdata WHERE YEAR(purchase_date) = $filter GROUP BY month ORDER BY month";
            
        } else {
            $filter = 2016;
            $yearFilter = "SELECT MONTH(purchase_date) month, SUM(grand_total) as total_sales,SUM(shipping) as shipping, SUM(tax) as tax FROM salesdata  WHERE YEAR(purchase_date) = $filter  GROUP BY month ORDER BY month";
        }
     
    ?>
    <div class="head">
        <h1>ADMIN PANEL</h1>
    </div>
    <div class="container">
        <div class="totals">
            <?php
                if($result = $mysqli->query($salesTotalOrdersUsers)){
                    if($result->num_rows > 0){
                        $row = $result->fetch_array();
                        echo '<div class="card-totals">';
                        echo '<h3>Total Sales</h3>';
                        echo '<h1 class="number">$'. sprintf("%0.2f",$row['total']).'</h1>';
                        echo '</div>';

                        echo '<div class="card-totals">';
                        echo '<h3>Total Orders</h3>';
                        echo '<h1 class="number">'.$row['totalOrders'].'</h1>';
                        echo '</div>';

                        echo '<div class="card-totals">';
                        echo '<h3>Total Users</h3>';
                        echo '<h1 class="number">'.$row['totalUsers'].'</h1>';
                        echo '</div>';
                    }
                }                   
            ?>
        </div>

        <div class="overview">
            <div class="top">
                <h3>Sales Overview</h3>
                <div class="filter">
                    <form action="/" method="post">
                        <select name="year" id="year" >
                            <?php
                                if($result = $mysqli->query($yearlySales)){
                                    if($result->num_rows > 0){
                                        while ($row = $result->fetch_assoc()){
                                            if ($row['year'] == $filter)
                                            {
                                                $selected = 'selected="selected"';
                                            }
                                            else
                                            {
                                                $selected = '';
                                            }
                                            // <option value="2016 selected=" selected""="">2016</option>
                                            // <option value="January" selected="selected">January</option>
                                            echo '<option value="'.$row['year'].'"'.$selected.'>'.$row['year'].'</option>';
                                        }
                                    }
                                }
                            ?>
                        </select>
                        <input type="submit" value="Filter">
                    </form>
                </div>
            </div>
            <div class="canvas">
                <canvas id="mySecondChart" aria-label="Sales, Tax and Shipping"></canvas>
            </div>
        </div>

        <div class="graphs">
            <div class="card-graphs">
                <div class="canvas">
                    <h3>Top Buying Customers</h3>
                    <canvas id="top5Customers" aria-label="Top Buying Customers"></canvas>
                </div>
            </div>
            <div class="card-graphs">
                <h3>Province Sales</h3>
                <div class="canvas">
                    <canvas id="provinceSales" aria-label="Province Sales"></canvas>
                </div>
            </div>
        </div>

        <div class="activity">
            <div class="card-activity">
                <h3>Recent Activity</h3>
                <hr>
                <?php
                    if($result = $mysqli->query($data)){
                        if($result->num_rows > 0){
                            while ($data = $result->fetch_assoc()){
                                echo '<h5>'. $data['order_id'] .'</h5>';
                                echo '<p>'. $data['cust_fname'] .' from '. $data['cust_province'].' has made a purchase of $'.sprintf("%0.2f",$data['grand_total']).'</p>';
                            }
                        }
                    }
                ?>
            </div>
            <div class="card-activity">
                <section>
                    <div class="tbl-header">
                        <table cellpadding="0" cellspacing="0" border="0">
                            <thead>
                                <tr>
                                    <th>Firstname</th>
                                    <th>Province</th>
                                    <th>City</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="tbl-content">
                        <table cellpadding="0" cellspacing="0" border="0" style="scrollbar-color: #87ceeb #ff5621;">
                            <tbody>
                            <?php
                                if($result = $mysqli->query($totalUsers)){
                                    if($result->num_rows > 0){
                                        while ($user = $result->fetch_assoc()){
                                            echo '<tr>';
                                            echo '<td>'.$user['cust_fname'].'</td>';
                                            echo '<td>'.$user['cust_province'].'</td>';
                                            echo '<td>'.$user['cust_city'].'</td>';
                                            echo '</tr>';
                                        }
                                    }
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</body>
</html>

<script>
    // Month Array for Chart
var month = ["January", 
             "February", 
             "March",
             "April",
             "May",
             "June",
             "July",
             "August",
             "September",
             "October",
             "November", 
             "December"
            ]
 
//Declaring variables to store the month infomation
var yearFilter = <?php 
                    if($result = $mysqli->query($yearFilter)){
                        if($result->num_rows > 0){
                            $row = $result->fetch_all();
                            echo json_encode($row);
                        }
                    }
                 ?>;
                 
var monthName = [];
var monthSales = [];
var monthShipping = [];
var monthtax = [];

monthName = month;
for (let i=0; i < monthName.length; ++i) {
    monthSales[i] = 0.0
    monthShipping[i] = 0.0
    monthtax[i] = 0.0
}
for (let i = 0; i < yearFilter.length; i++) {
    index = parseInt(yearFilter[i][0]) - 1
    monthSales[index] = parseFloat(yearFilter[i][1]).toFixed(2);
    monthShipping[index] = parseFloat(yearFilter[i][2]).toFixed(2);
    monthtax[index] = parseFloat(yearFilter[i][3]).toFixed(2);


    
}
//Declaring variables to store the Province infomation
var provinces = <?php 
                    if($result = $mysqli->query($provinces)){
                        if($result->num_rows > 0){
                            $row = $result->fetch_all();
                            echo json_encode($row);
                        }
                    }
                ?>;
var provinceName = [];
var provinceSales = [];
for (let i = 0; i < provinces.length; i++) {
                provinceName[i] = provinces[i][0];
                provinceSales[i] = parseFloat(provinces[i][1]).toFixed(2);
            }

// //Declaring variables to store the Top customer infomation
var topcustomers = <?php 
                    if($result = $mysqli->query($topcustomers)){
                        if($result->num_rows > 0){
                            $row = $result->fetch_all();
                            echo json_encode($row);
                        }
                    }
                ?>;

var customerName = [];
var customerSales = [];
for (let i = 0; i < topcustomers.length; i++) {
    customerName[i] = topcustomers[i][0];
    customerSales[i] = parseFloat(topcustomers[i][1]).toFixed(2);
}

//PROVINCE SALES CHART
var ctx = document.getElementById('provinceSales').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: provinceName,
        datasets: [{
            label: 'Amount in Sales (CAD $)',
            data: provinceSales,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(255, 99, 132, 1)',
            ],
            borderWidth: 1
        }]
    },
    
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
// TOP CUSTOMERS CHART
var ctx = document.getElementById('top5Customers').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: customerName,
        datasets: [{
            label: 'Amount in Sales (CAD $)',
            data: customerSales,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
        }]
    },
    
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});   
    //SALES OVERVIEW CHART
var ctx = document.getElementById('mySecondChart').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: monthName,
        datasets: [{
            label: 'Amount in Sales (CAD $)',
            data: monthSales,
            lineTension: 0.6,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1,
            fill: 'start'
        },
        {
            label: 'Amount in Shipping (CAD $)',
            data:monthShipping,
            lineTension: 0.6,
            backgroundColor: [
                'rgba(54, 162, 235, 0.2)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1,
            fill: 'start'
        },
        {
            label: 'Amount in Tax (CAD $)',
            data:monthtax,
            lineTension: 0.6,
            backgroundColor: [
                'rgba(255, 206, 86, 0.2)'
            ],
            borderColor: [
                'rgba(255, 206, 86, 1)'
            ],
            borderWidth: 1,
            fill: 'start'
        }]
    },
    
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>


