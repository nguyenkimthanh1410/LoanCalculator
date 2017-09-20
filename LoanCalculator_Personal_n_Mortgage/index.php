<html>
<head>
    <title>Form Front-end</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="container user_input">
    <h1>Loan Calculator</h1>
        <form class="well form-horizontal" id="form_input" action="calcLoan.php" method="post">
        <table border="0">
            <tr >
                <!--This value will affect daily interest rate-->
                <th>Loan Type:</th>
                <td colspan="3">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="loan_type" id="loan_type" value="personal"
                               title="Payment @beginning each period" checked="checked"> Personal Loan
                    </label>

                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="loan_type" id="loan_type" value="mortgage"
                               title="Payment @beginning each period"> Housing Mortgage
                    </label>

                    <!-- https://v4-alpha.getbootstrap.com/components/forms/#inline
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio2" value="option2"> 2
                    </labe>               -->
                </td>
            </tr>

            <tr>
                <td></td>
                <td class="divider"></td>
            </tr>

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
                <th>Quoted Annual Rate APR(%): </th>
                <td><input class="form-control" name="quoted_apr"
                           placeholder="10" id="quoted_apr"
                           onclick="validate_between('quoted_apr',0,100)" required>
                </td>
            </tr>

            <tr hidden>
                <th>Points(%):</th>
                <td>
                    <input class="form-control" class="digits" title="Points are only applied to Fixed Payment method.
                    In Other amortization methods, this value will be reset to zero"
                           name="points" id="points" value="0"
                           type="number" min="0" max="100" />
                </td>
            </tr>


            <tr class="required">
                <th>Loan Amount($):</th>
                <td><input class="form-control" type="number" name="loan_amt" placeholder="50000" required></td>

            </tr>

            <tr class="required">
                <th>Loan Term:</th>
                <td>
                    <input class="num_ptm" type="number" min="0" max="30" name="num_pmts_years" placeholder="1" required> years
                    <br />
                    <input class="num_ptm" type="number" min="0" max="12" name="num_pmts_months" placeholder="0" required> months
                </td>
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
                        <option value="1 month" selected="selected">Monthly</option>
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


            <tr hidden>
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

            <tr>
                <td><hr /></td>
                <td><hr /></td>
            </tr>

            <tr>
                <div lass="col-md-4">
                    <td></td>
                    <td align="left"><input class="btn" type="submit" value="Calculate Now"></td>
                </div>
            </tr>
        <table>
    </form>

</div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="app.js"></script>
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

/*
    //-----------------Learn about validate later----------
    function validate_between(id_str,min_exclusive,max_exclusive) {
         var x = parseFloat(document.getElementById(id_str).value);
         if(isNaN(x) || (x < min_exclusive) || (x > max_exclusive)) {
             // value is out of range
             alert("Value must be in range" + min_exclusive + " and " + max_exclusive + " (exclusive)");
             document.getElementById(id_str).value = 1.00;
             return false;
         }
         return true;
     }
      $(document).ready(function(){

        $('#form_input').formValidation({

            fields: {
                'quoted_apr': {
                    verbose: false,
                    validators: {
                        between: {
                            min: 0,
                            max: 100,
                            message: 'The percentage must be between 0 and 100'
                        }
                    }
                }
            }
        });
    });
*/
</script>

</html>


<?php
?>