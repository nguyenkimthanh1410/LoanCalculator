<html>
<head>
    <title>Form Front-end</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="container">
    <h1>Amortization Calculator</h1>
        <form class="well form-horizontal" id="form_input" action="calcLoan.php" method="post">
        <table border="0">
            <tr >
                <!--This value will affect daily interest rate-->
                <th>Days in year:</th>
                <td colspan="3">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="num_days_in_year" id="num_days_in_year"
                               checked="checked" title="It affects daily interest rate" value="360"> 360
                    </label>

                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="num_days_in_year" id="num_days_in_year"
                               title="It affects daily interest rate" value="364"> 364
                    </label>

                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="num_days_in_year" id="num_days_in_year"
                               title="It affects daily interest rate" value="365"> 365
                    </label>
                </td>
            </tr>

            <tr class="required">
                <th>Annual Interest Rate APR(%): </th>
                <td><input class="form-control" name="quoted_apr"
                           placeholder="10" id="quoted_apr" required>
                </td>
            </tr>

            <tr>
                <th>Points(%):</th>
                <td>
                    <input class="form-control" class="digits" title="Points indicates deduction of APR"
                           name="points" id="points" value="0"
                           type="number" min=0 max=100" />
                </td>
            </tr>


            <tr class="required">
                <th>Loan Amount($):</th>
                <td><input class="form-control" type="number" name="loan_amt" placeholder="50000" required></td>

            </tr>


            <tr class="required">
                <th>Number of payments:</th>
                <td><input class="form-control" type="number" name="num_pmts" placeholder="12" required></td>
            </tr>

            <tr class="required">
                <th>Loan date(mm/dd/yyyy):</th>
                <td>
                    <input class="form-control" type="date" name="loan_date" required value="<?php echo date('Y-m-d');?>">
                </td>
            </tr>

            <tr class="required">
                <th>First Payment Due(mm/dd/yyyy):</th>
                <td>
                    <input class="form-control" type="date" name="first_pmt_due" required value="<?php echo date('Y-m-d');?>">
                </td>
            </tr>

            <tr>
                <th>Payment Frequency:</th>
                <td>
                    <select class="form-control selectpicker" name="selected_pmt_interval">
                        <option value="1 day">Daily</option>
                        <option value="1 week">Weekly</option>
                        <option value="2 weeks">Bi-weekly</option>
                        <option value="4 weeks">Every 4 weeks</option>
                        <option value="1 month" selected="selected">Monthly</option>
                        <option value="2 months">Bi-monthly</option>
                        <option value="3 months">Quarterly</option>
                        <option value="4 months">Every 4 months</option>
                        <option value="6 months">Semi-annually</option>
                        <option value="12 months">Annually</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th>Compounding Period:</th>
                <td>
                    <select class="form-control selectpicker" name="selected_comp_interval">
                        <option value="1 day">Daily</option>
                        <option value="1 week">Weekly</option>
                        <option value="2 weeks">Bi-weekly</option>
                        <option value="4 weeks">Every 4 weeks</option>
                        <option value="1 month" selected="selected">Monthly</option>
                        <option value="2 months">Bi-monthly</option>
                        <option value="3 months">Quarterly</option>
                        <option value="4 months">Every 4 months</option>
                        <option value="6 months">Semi-annually</option>
                        <option value="12 months">Annually</option>
                    </select>
                </td>
            </tr>


            <tr>
                <!--http://homeguides.sfgate.com/straightline-vs-mortgagestyle-amortization-87743.html-->
                <th>Amortization Method:</th>
                <td>
                    <select class="form-control selectpicker" name="amort_method" id="amort_method">
                        <option selected="selected" value="fixed_pmt">Fixed Payment</option>
                        <option value="rule_78">Rule of 78</option>
                        <option value="canadian_mortgage">Canadian</option>
                        <option value="fixed_principal">Fixed Principal</option>
                        <option value="interest_only">Interest Only</option>
                        <option value="no_interest">No Interest</option>
                    </select>
                </td>
            </tr>

            <!-- This part should consider whether need to use or not,
            as it rarely happens in reality-->
            <tr>
                <th>Interest on Odd days interest:</th>
                <td>
                    <input type="checkbox" name="has_interest_on_odd_days_interest">
                </td>
            </tr>

            <tr>
                <td><hr /></td>
                <td><hr /></td>
            </tr>

            <tr>
                <div class="col-md-4">
                    <td></td>
                    <td align="left"><input class="btn" type="submit" value="Calculate Now" name="submit" id="submitId"></td>
                </div>
            </tr>
            <tr>
                <div>
                    <td></td>
                    <td id="errmsg"></td>
                </div>
            </tr>
        <table>
    </form>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<!---
<script src="app.js"></script>
-->

</body>

<script type="text/javascript">

    //https://jsfiddle.net/taditdash/8FHwL/
    function getdate() {
        var tt = document.getElementById('txtDate').value;

        var date = new Date(tt);
        var newdate = new Date(date);

        newdate.setDate(newdate.getDate() + 3);

        var dd = newdate.getDate();
        var mm = newdate.getMonth() + 1;
        var y = newdate.getFullYear();

        var someFormattedDate = mm + '/' + dd + '/' + y;
        document.getElementById('follow_Date').value = someFormattedDate;
    }

    //Tooltip: http://jquerytools.github.io/demos/tooltip/form.html
    // select all desired input fields and attach tooltips to them
    $("#form_input :input").tooltip({

        // place tooltip on the right edge
        position: "center right",

        // a little tweaking of the position
        offset: [-2, 10],

        // use the built-in fadeIn/fadeOut effect
        effect: "fade",

        // custom opacity setting
        opacity: 0.7

    });

    function string_to_date(str){
        var date_num = str.split('-');
        return new Date(parseInt(date_num[0]),parseInt(date_num[1]),parseInt(date_num[2]));
    }

    $('#form_input').submit(function( event ) {
        try{
            var res = true;
            var data = {};
            $.each($( this ).serializeArray(), function( index, value ) {
                data[value['name']]=value['value']
            });
            $('#errmsg').html("");
            console.log(data);
            if (!($.isNumeric(data['quoted_apr']) && data['quoted_apr']>=0 && data['quoted_apr']<=100)){
                $('#errmsg').append("<p>APR must be a number between 0 to 100</p>");
                res = false;
            }

            if (!($.isNumeric(data['points']) && data['points']>=0 && data['points']<=100)){
                $('#errmsg').append("<p>Points must be a number between 0 to 100</p>");
                res = false;
            }

            if (!($.isNumeric(data['loan_amt']) && data['loan_amt']>=0)){
                $('#errmsg').append("<p>Loan amount must be a positive number</p>");
                res = false;
            }

            if (!($.isNumeric(data['num_pmts']) && data['num_pmts']>=0 && data['num_pmts']<=100)){
                $('#errmsg').append("<p>Number of payments must be a positive number</p>");
                res = false;
            }

            if((string_to_date(data['first_pmt_due']) - string_to_date(data['loan_date'])) < 0){
                $('#errmsg').append("<p>The first payment due date must be later than the loan date</p>");
                res = false;
            }
            return res;
        }catch(e){
            console.log(e)
        }
        return false
    });

</script>








<!--
<script>
    $("#submitId").click(function(){
        var Serialized =  $("#form_input").serialize();
        $.ajax({
            type: "POST",
            url: "calcLoan.php",
            dataType: "JSON",
            success: function(json) {
                var obj = jQuery.parseJSON(data);// if the dataType is not specified as json uncomment this
                //do what ever you want with the server response
                console.log(obj);
                // $('#result').html(data);
                /*
                for(var i=0;i<json.length;i++){
                    $('#result').append(json[i].item_id)
                    echo   obj[i];
                }
                */

            },
            error: function(){
                alert('error handing here');
            }
        });
    });
</script>

-->


</html>

