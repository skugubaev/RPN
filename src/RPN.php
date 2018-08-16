<?php 

Class RPN {
    
    private $stack = [];
    private $numeric = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.'];
    private $operations = ['-', '+', '*', '/'];
    private $input = '';
    
    private function prepareInput($str) 
    {
        return str_replace(',', '.', $str);
    }
    
    private static function validate($str)
    {
        if (mb_stripos($str, '(') === false && mb_stripos($str, ')') === false) {
            return true;
        }
        
        $count = 0;
        $len = mb_strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $symbol = mb_substr($str, $i, 1);
            if ($symbol === '(') {
                $count++;
            }
            if ($symbol === ')') {
                $count--;
            }
            if ($count < 0) {
                break;
            }
        }
        return $count === 0;
    }

    private function convertToReversePolishNotation($str)
    {
        $this->stack = [];
        
        $result = '';
        $i = 0;
        $length = mb_strlen($str);
        
        while ($i < $length) {
            $symbol = mb_substr($str, $i, 1);;
            switch ($symbol) {
                case ' ': 
                    $i++;
                    continue;
                case '(': 
                    array_push($this->stack, $symbol);
                    $i++;
                    continue;
                case ')':
                    $operand = array_pop($this->stack);
                    while ($operand !=  '(') {
                        $result = $result . $operand . ' ';
                        $operand = array_pop($this->stack);
                    }
                    $i++;
                    continue;
                default:
                    if (in_array($symbol, $this->numeric)) {
                        $operand = '';
                        while (($i < $length) && in_array($symbol, $this->numeric)) {
                            $operand = $operand . $str[$i];
                            $i++;
                            $symbol = mb_substr($str, $i, 1);
                        }
                        $result = $result . $operand . ' ';
                    } elseif (in_array($symbol, $this->operations)) {
                        if (empty($this->stack)) {
                            array_push($this->stack, $symbol);
                        } else {
                            $weight = $this->getOperationWeight($symbol);
                            $operand = array_pop($this->stack);

                            if ($weight <= $this->getOperationWeight($operand)) {
                                while ($weight <= $this->getOperationWeight($operand)) {
                                    $result = $result . $operand . ' ';
                                    if (empty($this->stack)) { 
                                        break; 
                                    }
                                    $operand = array_pop($this->stack);
                                }
                                if ($weight > $this->getOperationWeight($operand)) {
                                    array_push($this->stack, $operand);
                                }
                            } else {
                                array_push($this->stack, $operand);
                            }
                            array_push($this->stack, $symbol);
                        }
                        $i++;
                        continue;
                    } else {
                        $i++;
                        throw new Exception("Unexpected symbol $symbol on postion $i \n\n");
                    }
            }

        }

        if (!empty($this->stack)) {
            $result = $result . implode(' ', $this->stack);
        }

        return $result;
    }

    private function getOperationWeight($operation)
    {
        $result = 0;
        switch ($operation) {
            case '-':
                $result = 2;
                break;
            case '+':
                $result = 2;
                break;
            case '*':
                $result = 2;
                break;
            case '/':
                $result = 2;
                break;
            case '(':
                $result = 1;
                break;
        }
        
        return $result;
    }

    private function calculateReversePolishNotation($str)
    {
        $this->stack = [];
        $token = strtok($str, ' ');
        while ($token !== false) {
            if (in_array($token, $this->operations)) {
                $b = array_pop($this->stack);
                $a = array_pop($this->stack);
                if ($a === null || $b === null) {
                    throw new Exception("Incorrect expretion {$this->input} \n\n");
                }
                switch ($token) {
                    case '*': 
                        $result = $a*$b; 
                        break;
                    case '/': 
                        if ($b == 0) {
                            throw new Exception("Division by zero \n\n");
                        }
                        $result = $a/$b; 
                        break;
                    case '+': 
                        $result = $a+$b; 
                        break;
                    case '-': 
                        $result = $a-$b; 
                        break;
                    default : 
                        throw new Exception("Unexpected token $token \n\n");
                }
                array_push($this->stack, $result);
            } elseif (is_numeric($token)) {
                array_push($this->stack, $token);
            }

            $token = strtok(' ');
        }
        $result = array_pop($this->stack);
        $error = array_pop($this->stack);
        if (!empty($error)) {
            throw new Exception("Incorrect expretion {$this->input} \n\n");
        }

        return round($result, 8);
    }
    
    public function calculateRPN($string)
    {
        if (!$this->validate($string)) {
            throw new Exception("Count open and close brackets didn't match {$this->input} \n\n");
        }
        
        $this->input = $string;
        $str = $this->prepareInput($string);
        $rpn = $this->convertToReversePolishNotation($str);
        $result = $this->calculateReversePolishNotation($rpn);
        
        echo "Skugubaev says, that $string = $result \n";
    }
}