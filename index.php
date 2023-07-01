<html>
<head>
  <title>Bowling Scores</title>
  <link rel=stylesheet href="css/bowling.css">

  <!-- Google Analytics added 27 May 2015, updated 1 July 2023 -->
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-9CQ0J7FJR9"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-9CQ0J7FJR9');
  </script>

</head>
<body>

<?php

  $dir = "data";

  if (!isset($_GET['f'])) {
    $files = scandir($dir);
    echo "<h1>Bowling Scores</h1>";
    echo "\n\n<ul>";
    foreach ($files as $file) {
      if(preg_match("/\d{4}-\d{2}-\d{2}/", $file, $matches)) {
        echo "\n<li><a href=\"?f=".$matches[0]."\">".$matches[0]."</a></li>";
      }
    }
    echo "\n</ul>";
  }
  else {
    $date = $_GET['f'];

    $outing = new Outing($dir."/".$date.".txt", $date);
    echo $outing->prettyPrint();
  }



  class Outing {
    private $filename;
    private $date;
    private $raw;
    private $matches = array();

    public function __construct($file, $date) {
      $this->filename = $file;
      $this->date = $date;
      $this->raw = @file_get_contents($file);
      if ($this->raw == FALSE) {
        echo "<div class=\"error\"><b>Error:</b> No such file.</div>";
        exit();
      }

      // Split into matches
      $matches = preg_split("/\n\n/", $this->raw);
      $matchNum = 1;
      foreach ($matches as $match) {
        if ($match != '') {
          $this->matches[$matchNum++] = new Match($match);
        }
      }
    }

    public function match($i) {
      return $this->matches[$i];
    }

    public function __toString() {
      return "<pre>".($this->raw)."</pre>";
    }
    public function prettyPrint() {
      $r = "<h1 class=\"date\">".$this->date."</h1>";
      $r .= "\n\n<a href=\"./\">&laquo; Back</a> | ";
      $r .= "<a href=\"".$this->filename."\" target=\"_blank\">Raw</a>";
      foreach ($this->matches as $matchNum => $match) {
        $r .= "\n\n<!-- ------------------------- GAME ".$matchNum." ------------------------- -->";
        $r .= "\n\n<hr>";
        $r .= "\n<h2 class=\"matchNum\">Game ".$matchNum."</h2>";
        $r .= $match->prettyPrint();
      }
      $r .= "\n\n<!-- ------------------------- END ------------------------- -->";
      $r .= "\n\n<hr>";
      return $r;
    }
  }

  class Match {
    private $raw;
    private $games = array();

    public function __construct($match) {
      $this->raw = $match;

      // Split into games
      $games = preg_split("/\n/", $this->raw);
      $gameNum = 1;
      foreach ($games as $game) {
        if ($game != '') {
          $this->games[$gameNum++] = new Game($game);
        }
      }
    }

    public function game($i) {
      return $this->games[$i];
    }
    
    public function __toString() {
      return "<pre>".($this->raw)."</pre>";
    }
    public function prettyPrint() {
      $r = "\n\n<table class=\"match\">";
      $r .= "\n<tr class=\"frameNum\">";
      $r .= "<th></th>";
      for ($i = 1; $i <= 10; $i++) {
        $r .= "<th>".$i."</th>";
      }
      $r .= "<th></th>";
      $r .= "</tr>";
      foreach ($this->games as $game) {
        $r .= $game->prettyPrint();
      }
      $r .= "\n\n</table>";
      return $r;
    }
  }

  class Game {
    private $raw;
    private $player;
    private $frames = array();

    private $frameScores = array();
    private $cumulativeScores = array();
    private $gameScore;

    public function __construct($game) {
      $this->raw = $game;

      // Split into frames
      $frames = preg_split("/\t/", $this->raw);
      $this->player = $frames[0];
      unset($frames[0]);
      foreach ($frames as $frameNum => $frame) {
        $this->frames[$frameNum] = new Frame($frameNum, $frame);
      }

      // Score each frame, and sum them
      $total = 0;
      foreach ($this->frames as $frameNum => $frame) {
        $frameScore = $this->scoreFrame($frameNum);

        $this->frameScores[$frameNum] = $frameScore;
        $total += $frameScore;
        $this->cumulativeScores[$frameNum] = $total;
      }
      $this->gameScore = $this->cumulativeScores[10];
    }

    public function player() {
      return $this->player;
    }
    public function frame($i) {
      return $this->frames[$i];
    }

    public function scoreFrame($frameNum) {
      $frameResult = $this->frame($frameNum)->result();

      switch ($frameResult) {
        case '0':
          return 0;
          break;
        case 'X':
          return $this->scoreStrike($frameNum);
          break;
        case '/':
          return $this->scoreSpare($frameNum);
          break;
        default:
          return $frameResult;
          break;
      }
    }

    private function scoreSpare($frameNum) {
      if ($frameNum != 10) {
        $nextBall1 = $this->frame($frameNum+1)->ball(1)->value();
      }
      else {
        $nextBall1 = $this->frame(10)->ball(3)->value();
      }

      $pins = 0;
      switch ($nextBall1) {
        case 'X':
          $pins = 10;
          break;
        case '-':
          $pins = 0;
          break;
        default:
          $pins = $nextBall1;
          break;
      }
      return 10 + $pins;
    }

    private function scoreStrike($frameNum) {
      if ($frameNum != 10) {
        $nextBall1 = $this->frame($frameNum+1)->ball(1)->value();
        if ($this->frame($frameNum+1)->countBalls() >= 2) {
          $nextBall2 = $this->frame($frameNum+1)->ball(2)->value();
        }
        else {
          $nextBall2 = $this->frame($frameNum+2)->ball(1)->value();
        }
      }
      else {
        $nextBall1 = $this->frame(10)->ball(2)->value();
        $nextBall2 = $this->frame(10)->ball(3)->value();
      }

      $pins = 0;
      switch ($nextBall1) {
        case 'X':
          $pins = 10;
          break;
        case '-':
          $pins = 0;
          break;
        default:
          $pins = $nextBall1;
          break;
      }
      switch ($nextBall2) {
        case 'X':
          $pins += 10;
          break;
        case '/':
          $pins = 10;  // Total is 10 if second balls makes a spare
          break;
        case '-':
          $pins += 0;
          break;
        default:
          $pins += $nextBall2;
          break;
      }
      return 10 + $pins;
    }
    
    public function __toString() {
      return "<pre>".($this->raw)."</pre>";
    }
    public function prettyPrint() {
      $r = "\n\n<tr class=\"game\">";
      $r .= "\n<th class=\"player\">".$this->player."</th>";
      foreach ($this->frames as $frameNum => $frame) {
        $r .= "\n".$frame->prettyPrint($this->frameScores[$frameNum], $this->cumulativeScores[$frameNum]);
      }
      $r .= "\n<th class=\"total\">".$this->gameScore."</th>";
      $r .= "\n</tr>";
      return $r;
    }
  }

  class Frame {
    private $raw;
    private $frameNum;
    private $balls = array();

    public function __construct($frameNum, $frame) {
      $this->raw = $frame;
      $this->frameNum = $frameNum;

      // Split into balls
      preg_match_all("/.s?/", $this->raw, $balls);
      $ballNum = 1;
      foreach ($balls[0] as $ball) {
        $this->balls[$ballNum++] = new Ball($ball);
      }
    }

    public function ball($i) {
      return $this->balls[$i];
    }

    public function countBalls() {
      return count($this->balls);
    }
    public function result() {
      foreach ($this->balls as $ballNum => $ball) {
        $value = $this->ball($ballNum)->value();

        // If ball was strike or spare, that is the result of the frame
        if ($value == 'X' || $value == '/') {
          return $value;
        }
        if ($value == '-') {
          $value = 0;
        }
        $ballValue[$ballNum] = $value;
      }
      return array_sum($ballValue);
    }

    public function __toString() {
      return "<pre>".($this->raw)."</pre>";
    }
    public function prettyPrint($frameScore, $cumulativeScore) {
      $r = "<td class=\"frame frame".$this->frameNum."\">";
      $r .= "<div class=\"balls\">";
      foreach ($this->balls as $ball) {
        $r .= $ball->prettyPrint();
      }
      $r .= "</div>";
      $r .= "<div class=\"score\">".$cumulativeScore."</div>";
      $r .= "</td>";
      return $r;
    }
  }

  class Ball {
    private $raw;

    public function __construct($ball) {
      $this->raw = $ball;
    }

    public function value() {
      return substr($this->raw, 0, 1);
    }

    public function __toString() {
      return "<pre>".($this->raw)."</pre>";
    }
    public function prettyPrint() {
      $value = $this->value();
      if (substr($this->raw, -1) == 's') {
        return "<div class=\"ball split\" alt=\"".$value." split\">".$value."</div>";
      }
      switch ($value) {
        case "-":
          return "<div class=\"ball gutter\" alt=\"Gutter\">&ndash;</div>";
          break;
        case "/":
          return "<div class=\"ball spare\" alt=\"Spare\">/</div>";
          break;
        case "X":
          return "<div class=\"ball strike\" alt=\"Strike\">X</div>";
          break;
        default:
          return "<div class=\"ball\" alt=\"".$value."\">".$value."</div>";
          break;
      }
    }
  }
?>


</body>
</html>
