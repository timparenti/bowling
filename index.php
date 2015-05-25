<html>
<head>
  <title>Bowling Scores</title>
  <link rel=stylesheet href="css/bowling.css">
</head>
<body>
<?php
  $outing = new Outing("data/2015-05-22.txt");

  // DEBUGGING OUTPUT
  echo "<hr>";
  echo $outing;
  echo "<hr>";
  echo $outing->match(2)->prettyPrint();
  echo "<hr>";
  echo $outing->match(2)->game(3)->prettyPrint();
  echo "<hr>";
  echo $outing->match(1)->game(1)->frame(1)->prettyPrint();
  echo $outing->match(1)->game(1)->frame(4)->prettyPrint();
  echo $outing->match(2)->game(3)->frame(5)->prettyPrint();
  echo $outing->match(2)->game(3)->frame(10)->prettyPrint();
  echo $outing->match(2)->game(4)->frame(10)->prettyPrint();
  echo $outing->match(3)->game(2)->frame(10)->prettyPrint();
  echo "<hr>";
  echo $outing->match(2)->game(3)->frame(5)->ball(1)->prettyPrint();
  echo $outing->match(2)->game(3)->frame(5)->ball(2)->prettyPrint();
  echo $outing->match(4)->game(3)->frame(1)->ball(1)->prettyPrint();
  echo $outing->match(4)->game(3)->frame(5)->ball(1)->prettyPrint();
  echo $outing->match(4)->game(3)->frame(5)->ball(2)->prettyPrint();
  echo "<hr>";


  class Outing {
    private $raw;
    private $matches = array();

    public function __construct($file) {
      $this->raw = file_get_contents($file);

      // Split into matches
      $matches = preg_split("/\n\n/", $this->raw);
      $matchNum = 1;
      foreach ($matches as $match) {
        $this->matches[$matchNum++] = new Match($match);
      }
    }

    public function match($i) {
      return $this->matches[$i];
    }

    public function __toString() {
      return "<pre>".($this->raw)."</pre>";
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
        $this->games[$gameNum++] = new Game($game);
      }
    }

    public function game($i) {
      return $this->games[$i];
    }
    
    public function __toString() {
      return "<pre>".($this->raw)."</pre>";
    }
    public function prettyPrint() {
      $r = "<table class=\"match\">";
      $r .= "<tr>";
      $r .= "<th></th>";
      for ($i = 1; $i <= 10; $i++) {
        $r .= "<th class=\"frameNum\">".$i."</th>";
      }
      $r .= "<th></th>";
      $r .= "</tr>";
      foreach ($this->games as $game) {
        $r .= $game->prettyPrint();
      }
      $r .= "</table>";
      return $r;
    }
  }

  class Game {
    private $raw;
    private $player;
    private $frames = array();

    public function __construct($game) {
      $this->raw = $game;

      // Split into frames
      $frames = preg_split("/\t/", $this->raw);
      $this->player = $frames[0];
      unset($frames[0]);
      foreach ($frames as $frameNum => $frame) {
        $this->frames[$frameNum] = new Frame($frameNum, $frame);
      }
    }

    public function player() {
      return $this->player;
    }
    public function frame($i) {
      return $this->frames[$i];
    }
    
    public function __toString() {
      return "<pre>".($this->raw)."</pre>";
    }
    public function prettyPrint() {
      $r = "<tr class=\"game\">";
      $r .= "<th class=\"player\">".$this->player."</th>";
      foreach ($this->frames as $frame) {
        $r .= "<td>".$frame->prettyPrint()."</td>";
      }
      $r .= "<th class=\"total\"></th>";
      $r .= "</tr>";
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

    public function __toString() {
      return "<pre>".($this->raw)."</pre>";
    }
    public function prettyPrint() {
      if ($this->frameNum == 10) {
        $r = "<div class=\"frame frame10\">";
      }
      else {
        $r = "<div class=\"frame\">";
      }
      $r .= "<div class=\"balls\">";
      foreach ($this->balls as $ball) {
        $r .= $ball->prettyPrint();
      }
      $r .= "</div>";
      $r .= "</div>";
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
