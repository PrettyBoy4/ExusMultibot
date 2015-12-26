<?php
/** clock(multibotCore)
  *
  * Wersja: Alpha 1.1.3
  * Data wydania: 25.11.2015
  *
  * Tworzy godzine ze spacerow podanych w konfiguracji
  *
  */
class clock extends start
{
  private $multibotCore;
  protected $timer = 0;
  private $time;
  private $numbers;


/** function_start()
  *
  * Typ: Chroniona
  *
  *
  *
  */
protected function start_function()
{
  if(empty($this->time))
  {
    $this->setTime();
    $this->time = date("r");
  }

  if($this->time < date("r"))
  {
    $this->setTime();
    $this->time < date("r");
  }
  return 'Clock';
}

/** setTime()
  *
  * Typ: Chroniona
  *
  * Ustawia nazwy kanałów tak aby wyśietlały aktualną godzinę.
  *
  */
  protected function setTime()
  {
    $channels = explode(",", $this->multibotCore->config['multibotConfig']['clock']['spacers']);
    $i = 0;

    $hour = date("H");
    $minutes = date("i");
    foreach($channels as $temp)
    {
      if(strlen(date("H")) == 2)
      {
        $channelProperties['channel_name'] = "[cspacer]".$this->numbers[$hour{0}][$i]."─".$this->numbers[$hour{1}][$i]."─".$this->numbers[10][$i]."─".$this->numbers[$minutes{0}][$i]."─".$this->numbers[$minutes{1}][$i];
      }
      elseif(strlen(date("H")) == 1)
      {
        $channelProperties['channel_name'] = "[cspacer]".$this->numbers[$hour][$i]."─".$this->numbers[10][$i]."─".$this->numbers[$minutes{0}][$i]."─".$this->numbers[$minutes{1}][$i];
      }

      $this->multibotCore->tsAdmin->channelEdit($temp, $channelProperties);
      $i++;
    }
    $this->time = date("H:i");
  }

  /** __construct($multibotCore)
    *
    * Typ: Konstruktor
    *
    * Przypisuje referencje
    *
    */
    function __construct(multibotCore $multibotCore)
    {
      $this->multibotCore = $multibotCore;

      $number0[] = '▄▀▀▀▄';
      $number0[] = '█───█';
      $number0[] = '█───█';
      $number0[] = '▀▄▄▄▀';

      $number1[] = '─▄█─';
      $number1[] = '▀─█─';
      $number1[] = '──█─';
      $number1[] = '──█─';

      $number2[] = '▄▀▀▀▄';
      $number2[] = '───▄▀';
      $number2[] = '─▄▀──';
      $number2[] = '█▄▄▄▄';

      $number3[] = '▄▀▀▀▄';
      $number3[] = '──▄▄▀';
      $number3[] = '────█';
      $number3[] = '▀▄▄▄▀';

      $number4[] = '───▄█─';
      $number4[] = '─▄▀─█─';
      $number4[] = '█▄▄▄█▄';
      $number4[] = '────█─';

      $number5[] = '─█▀▀▀▀';
      $number5[] = '─█▄▄▄─';
      $number5[] = "─────█";
      $number5[] = '─▀▄▄▄▀';

      $number6[] = '▄▀▀▀▄';
      $number6[] = '█▄▄▄─';
      $number6[] = '█───█';
      $number6[] = '▀▄▄▄▀';

      $number7[] = '▀▀▀▀█';
      $number7[] = '───█─';
      $number7[] = '──█──';
      $number7[] = '─█───';

      $number8[] = '▄▀▀▀▄';
      $number8[] = '▀▄▄▄▀';
      $number8[] = '█───█';
      $number8[] = '▀▄▄▄▀';

      $number9[] = '▄▀▀▀▄';
      $number9[] = '▀▄▄▄▀';
      $number9[] = '────█';
      $number9[] = '▀▄▄▄▀';


      $number10[] = '───';
      $number10[] = '─▀─';
      $number10[] = '─▄─';
      $number10[] = '───';

      $this->numbers[0] = $number0;
      $this->numbers[1] = $number1;
      $this->numbers[2] = $number2;
      $this->numbers[3] = $number3;
      $this->numbers[4] = $number4;
      $this->numbers[5] = $number5;
      $this->numbers[6] = $number6;
      $this->numbers[7] = $number7;
      $this->numbers[8] = $number8;
      $this->numbers[9] = $number9;
      $this->numbers[10] = $number10;
    }
}
?>
