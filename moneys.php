<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function dd(...$values)
{
    foreach($values as $value) {
        var_dump($value);
    }
    exit;
}

function dump(...$values)
{
    foreach($values as $value) {
        var_dump($value);
    }
}

class Moneys {
    private $args;

    private $credit = [];

    private $reoccuring = [];

    private $bi_weekly_income = null;

    public function __construct($args)
    {
        $this->args = $args;
        $env = json_decode(file_get_contents('env.json'), 1);
        $this->reoccuring = [
            'fed_loan' => $env['REOCCURING_FED_LOAN'],
            'renee_loan' => $env['REOCCURING_RENEE_LOAN'],
            'car' => $env['REOCCURING_CAR'],
            'auto_insurance' => $env['REOCCURING_AUTO_INSURANCE'],
            'rent' => $env['REOCCURING_RENT'],
        ];
        $this->bi_weekly_income = $env['INCOME'];
        $this->credit = explode(',', $env['CREDIT_TYPES']);
    }

    public function run()
    {
        if(!isset($this->args[1])) {
            echo "run with php moneys.php month 04 or php moneys.php average_spending \n";
            exit;
        }
        $type = $this->args[1];
        switch($type) {
            case 'average_spending':
                    $this->averageSpending();
                break;
            case 'month':
                if (!isset($this->args[2])) {
                    echo 'need to pass in which month';
                } else if (!isset($this->args[3])) {
                    $this->runMonthSpending($this->args[2]);
                } else {
                    $this->runMonthSpending($this->args[2], $this->args[3]);
                }
                break;
        }

    }

    public function runMonthSpending(int $month, int $end_month = 0)
    {
        if ($end_month) {
            while($month <= $end_month) {
                $this->monthSpending($month, true);
                $month++;
            }
        } else {
            $this->monthSpending($month);
        }
    }

    public function monthSpending(int $month, $range = false)
    {
        $credit_spending = 0;
        $total_check_deposit = 0;
        $banks = [
            'boa' => [],
            'discover' => [],
            'chase' => [],
            'eversource' => []
        ];
        $found = false;
        $datas = $this->loadCsv();
        foreach($datas as $data) {
            $csv_month = (int) substr($data[0], 0, 2);
            $csv_day = (int) substr($data[0], 3, 5);

            if ($csv_month < $month || ($csv_month > $month && $csv_day > 5) || ($found == true) && $csv_month != $month) {
                continue;
            }
            // dump($data);
            if($this->contains($data[2], 'ALIPES CME')) {
                $this->bi_weekly_income = (float)$data[4];
            }
            if($this->contains($data[2], $this->credit[0])) {
                $banks['boa'][] = (float)$data[3] * -1;
            }
            if($this->contains($data[2], $this->credit[1])) {
                $banks['discover'][] = (float)$data[3] * -1;
            }
            if($this->contains($data[2], $this->credit[2])) {
                $banks['chase'][] = (float)$data[3] * -1;
            }
            if($this->contains($data[2], 'EVERSOURCE')) {
                $banks['eversource'][] = (float)$data[3] * -1;
            }
            if($this->contains($data[2], 'DEPOSIT') || ($this->contains($data[2], 'VENMO') && (float)$data[4] > 1000)) {
                $check_deposits[] = (float)$data[4];
            }

            $found = $this->allBanksFound($banks);
        }
        
        foreach($banks as $key => $bank) {
            if (empty($bank[0])) {
                continue;
            }
            $credit_spending += $bank[0];
        }

        if(!empty($check_deposits)) {
            foreach($check_deposits as $key => $deposit) {
                $total_check_deposit += $deposit;
            }
        }

        if(!$range) {
            var_dump($banks);
        }

        $monthly_income = $this->bi_weekly_income * 2;
        echo "Monthly income is $monthly_income \n";
        $reoccuring = $this->totalReoccuring();
        echo "your reoccuring payments equal $reoccuring \n";
        $credit_spending = round($credit_spending, 2);
        echo "you spent " . $credit_spending . " using credit cards \n";
        $total_spending = $credit_spending + $reoccuring;
        echo "your total check deposits is $total_check_deposit \n";
        echo "Your total spending is " . $total_spending . "\n";
        $total = (($this->bi_weekly_income * 2) + $total_check_deposit) - ($reoccuring + $credit_spending);
        echo "difference for month $month is " . round($total, 2) . "\n\n";
        
    }

    public function loadCsv()
    {
        $rows = array();
        if (($handle = fopen('spending.csv', "r")) !== FALSE) {
            while (($data = fgetcsv($handle)) !== FALSE) {
                array_push($rows, $data);
            }
            fclose($handle);
        }
        return array_reverse($rows);
    }

    public function allBanksFound($banks)
    {
        if (count($banks['boa']) > 0 
            && count($banks['discover']) > 0 
            && count($banks['chase']) > 0 
            // && count($banks['eversource']) > 0
        ) {
            return true;
        }
    }

    protected function averageSpending()
    {
        $average_spending = 0;
        $banks = [
            'boa' => [],
            'discover' => [],
            'chase' => []
        ];
        if (($handle = fopen("spending.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle)) !== FALSE) {
                if($this->contains($data[1], $this->credit[0])) {
                    $banks['boa'][] = $data[3];
                }
                if($this->contains($data[1], $this->credit[1])) {
                    $banks['discover'][] = $data[3];
                }
                if($this->contains($data[1], $this->credit[2])) {
                    $banks['chase'][] = $data[3];
                }
            }
            fclose($handle);
        }
        foreach($banks as $key => $bank) {
            echo "bank " . $key . ' averages ' . round(array_sum($bank) / count($bank),2) . "\n";
            $average_spending += array_sum($bank) / count($bank);

        }

        $reoccuring = $this->totalReoccuring();
        $total = ($this->bi_weekly_income * 2) - $reoccuring - $average_spending;
        echo "average total " . round($total, 2) . " for last 7 months \n";
    }

    private function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    public function totalReoccuring()
    {
        $sum = 0;
        foreach ($this->reoccuring as $value) {
            $sum += $value;
        }
        return $sum;
    }
}

$money = new Moneys($argv);
$money->run();