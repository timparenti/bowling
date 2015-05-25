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
  echo $outing->match(2);
  echo "<hr>";
  echo $outing->match(2)->game(3);
  echo "<hr>";
  echo $outing->match(2)->game(3)->frame(5);
  echo "<hr>";
  echo $outing->match(2)->game(3)->frame(5)->ball(1);
  echo $outing->match(2)->game(3)->frame(5)->ball(2);
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
  }

  class Ball {
    private $raw;

    public function __construct($ball) {
      $this->raw = $ball;
    }

    public function __toString() {
      return "<pre>".($this->raw)."</pre>";
    }
  }
?>
</body>
</html>
