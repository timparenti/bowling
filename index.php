<html>
<head>
  <title>Bowling Scores</title>
  <link rel=stylesheet href="css/bowling.css">
</head>
<body>

<?php
  $outing = new Outing("data/2015-05-22.txt");
  echo $outing->prettyPrint();



  class Outing {
    private $filename;
    private $raw;
    private $matches = array();

    public function __construct($file) {
      $this->filename = $file;
      $this->raw = file_get_contents($file);

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
      $r = "<h1 class=\"filename\">".$this->filename."</h1>";
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
      $r = "\n\n<tr class=\"game\">";
      $r .= "\n<th class=\"player\">".$this->player."</th>";
      foreach ($this->frames as $frame) {
        $r .= "\n".$frame->prettyPrint();
      }
      $r .= "\n<th class=\"total\"></th>";
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

    public function __toString() {
      return "<pre>".($this->raw)."</pre>";
    }
    public function prettyPrint() {
      $r = "<td class=\"frame frame".$this->frameNum."\">";
      $r .= "<div class=\"balls\">";
      foreach ($this->balls as $ball) {
        $r .= $ball->prettyPrint();
      }
      $r .= "</div>";
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
