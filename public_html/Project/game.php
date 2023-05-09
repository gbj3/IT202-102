<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<html>
<head>
    <script>
        //modified from http://jsfiddle.net/bencentra/q1s8gmqv/?utm_source=website&utm_medium=embed&utm_campaign=q1s8gmqv
        var canvas;
        var context;
        var loop;
        var leftPaddle;
        var rightPaddle;
        var ball;
        var paddleWidth = 25;
        var paddleHeight = 100;
        var ballSize = 10;
        var ballSpeed = 2;
        var paddleSpeed = 2;
        var drawables = [];
        // Key Codes
        var W = 87;
        var S = 83;
        var UP = 38;
        var DOWN = 40;

        // Keep track of pressed keys
        var keys = {
            W: false,
            S: false,
            UP: false,
            DOWN: false
        };
        function GameData() {
        return {
            score: 0,
            AddPoints: function(p) {
                this.score += p;
                document.getElementById("score").innerText = "Score: " + this.score;
            }
        }
    }
        // Keep track of the score

        var leftScore = 0;
        var rightScore = 0;
        function init() {
            canvas = document.getElementById("board");
            if (canvas.getContext) {
                context = canvas.getContext("2d");
                leftPaddle = makeRect(25, canvas.height / 2 - paddleHeight / 2, paddleWidth, paddleHeight, 5, '#BC0000'); 
                rightPaddle = makeRect(canvas.width - paddleWidth - 25, canvas.height / 2 - paddleHeight / 2, paddleWidth, paddleHeight, 5, '#0000BC');
                ball = makeRect(0, 0, ballSize, ballSize, ballSpeed, '#000000');
                drawables.push(leftPaddle);
                drawables.push(rightPaddle);
                drawables.push(ball);
                console.log(drawables);
                resetBall();
                attachKeyListeners();
                loop = window.setInterval(gameLoop, 16);
                canvas.focus();
            }
        }
        function resetBall() {
            leftPaddle.h = paddleHeight;
            rightPaddle.h = paddleHeight;
            leftPaddle.c = '#BC0000';
            rightPaddle.c = '#0000BC';
            ball.c = '#000000';
            ball.x = canvas.width / 2 - ball.w / 2;
            ball.y = canvas.height / 2 - ball.w / 2;
            // Modify the ball object to have two speed properties, one for X and one for Y
            ball.sX = ballSpeed;
            ball.sY = ballSpeed / 2;

            // Randomize initial direction
            if (Math.random() > 0.5) {
                ball.sX *= -1;
            }
            // Randomize initial direction
            if (Math.random() > 0.5) {
                ball.sY *= -1;
            }
        }
        // Bounce the ball off of a paddle
        function bounceBall() {
            // Increase and reverse the X speed
            let bounceCount = 0;
            if (ball.sX > 0) {
                let temp = ball.c;
                ball.c = rightPaddle.c;
                rightPaddle.c = temp;
                if (rightScore >= leftScore){
                    ball.sX += 1;
                }
                // Add some "spin"
                if (keys.UP) {
                    ball.sY -= 1;
                } else if (keys.DOWN) {
                    ball.sY += 1;
                }
                rightPaddle.h -= 5; //gbj3 IT202 4/26
            } else {
                let temp = ball.c;
                ball.c = leftPaddle.c;
                leftPaddle.c = temp;
                if (rightScore <= leftScore){
                    ball.sX -= 1;
                }
                // Add some "spin"
                if (keys.W) {
                    ball.sY -= 1;
                } else if (keys.S) {
                    ball.sY += 1
                }
                leftPaddle.h -= 5; //gbj3 IT202 4/26
            }
            ball.sX *= -1;
        }

        function attachKeyListeners() {
            // Listen for keydown events
            window.addEventListener('keydown', function (e) {
                console.log("keydown", e);
                if (e.keyCode === W) {
                    keys.W = true;
                }
                if (e.keyCode === S) {
                    keys.S = true;
                }
                if (e.keyCode === UP) {
                    keys.UP = true;
                }
                if (e.keyCode === DOWN) {
                    keys.DOWN = true;
                }
                console.log(keys);
            });
            window.addEventListener('keyup', function (e) {
                console.log("keyup", e);
                if (e.keyCode === W) {
                    keys.W = false;
                }
                if (e.keyCode === S) {
                    keys.S = false;
                }
                if (e.keyCode === UP) {
                    keys.UP = false;
                }
                if (e.keyCode === DOWN) {
                    keys.DOWN = false;
                }
                console.log(keys);
            });
        }
        // Create a rectangle object - for paddles, ball, etc
        function makeRect(x, y, width, height, speed, color) {
            if (!color)
                color = '#000000';
            return {
                x: x,
                y: y,
                w: width,
                h: height,
                s: speed,
                c: color,
                draw: function () {
                    context.fillStyle = this.c;
                    context.fillRect(this.x, this.y, this.w, this.h);
                }
            };
        }
        function doAI() {
            if (ball.x >= canvas.width * .6) {
                let paddleHalf = paddleHeight / 2;
                if (ball.y > rightPaddle.y + paddleHalf) {
                    rightPaddle.y += rightPaddle.s;
                } else if (ball.y < rightPaddle.y) {
                    rightPaddle.y -= rightPaddle.s;
                }
            }
            clampToCanvas(rightPaddle);
        }
        function movePaddle() {
            if (keys.W) {
                leftPaddle.y -= leftPaddle.s;
            }
            if (keys.S) {
                leftPaddle.y += leftPaddle.s;
            }
            if (keys.UP) {
                leftPaddle.y -= leftPaddle.s;
            }
            if (keys.DOWN) {
                leftPaddle.y += leftPaddle.s;
            }
            clampToCanvas(leftPaddle);
        }
        function clampToCanvas(paddle) {
            if (paddle.y < 0) {
                paddle.y = 0;
            }
            if (paddle.y + paddle.h > canvas.height) {
                paddle.y = canvas.height - paddle.h;
            }
        }
        function moveBall() {
            // Move the ball
            ball.x += ball.sX;
            ball.y += ball.sY;
            // Bounce the ball off the top/bottom
            if (ball.y < 0 || ball.y + ball.h > canvas.height) {
                ball.sY *= -1;
            }
        }
        function checkPaddleCollision() {
            // Bounce the ball off the paddles
            if (ball.y + ball.h / 2 >= leftPaddle.y && ball.y + ball.h / 2 <= leftPaddle.y + leftPaddle.h) {
                if (ball.x <= leftPaddle.x + leftPaddle.w) {
                    bounceBall();
                }
            }
            if (ball.y + ball.h / 2 >= rightPaddle.y && ball.y + ball.h / 2 <= rightPaddle.y + rightPaddle.h) {
                if (ball.x + ball.w >= rightPaddle.x) {
                    bounceBall();
                }
            }
        }
        function checkScore() { //gbj3 IT202
            // Score if the ball goes past a paddle
            if (ball.x < leftPaddle.x) {
                rightScore++;
                if (rightScore-leftScore >= 6){
                    leftScore++;
                }
                resetBall();
                ball.sX *= -1;
            } else if (ball.x + ball.w > rightPaddle.x + rightPaddle.w) {
                leftScore++;
                if (leftScore-rightScore >= 6){
                    rightScore++;
                }
                resetBall();
                ball.sX *= -1;
            }
        }
        function drawScores() {
            // Draw the scores
            context.fillStyle = '#000000';
            context.font = '24px Arial';
            context.textAlign = 'left';
            context.fillText('Score: ' + leftScore, 5, 24);
            context.textAlign = 'right';
            context.fillText('Score: ' + rightScore, canvas.width - 5, 24);
        }
        function erase() {
            context.fillStyle = '#FFFFFF';
            context.fillRect(0, 0, canvas.width, canvas.height);
        }

        function gameLoop() {
            erase();
            movePaddle();
            doAI();
            moveBall();

            checkPaddleCollision();
            checkScore();
            drawScores();
            //draw stuff
            for (let i = 0; i < drawables.length; i++) {
                drawables[i].draw();
            }
        }
    </script>
</head>
<body onload="init();">
<main>
    <canvas id="board" width="600px" height="600px" style="border: 1px solid black;">

    </canvas>
</main>
</body>
</html>
<?php require(__DIR__ . "/../../partials/footer.php");