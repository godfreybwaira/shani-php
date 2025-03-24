<?php

class A{

    private string $a;

    public function __construct(string $a){
        $this->a = $a;
    }

    public function setA(string $a){
        $this->a = $a;
    }

    public function printA(){
        echo $this->a;
        echo PHP_EOL;
    }
}

class B{

    private A $a;

    public function __construct(A $a){
        $this->a = $a;
    }

    public function getA(){
       return $this->a;
    }
}

$a = new A(5);
$b = new B($a);
$b->getA()->setA(10);
$a->printA();
