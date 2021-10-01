<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test</title>
    <style>
        #lernweg{

        }
        #lernweg svg{
            max-width: fit-content;
        }
        #lernweg svg text{
            font-family: sans-serif;
            font-size: 100%;

        }

    </style>
</head>
<body>
<div id="canvas">
	<?php
	include "draw-svg.php";
	?>

</div>
<script>

    Object.defineProperty(Array.prototype, 'shuffle', {
        value: function() {
            for (let i = this.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [this[i], this[j]] = [this[j], this[i]];
            }
            return this;
        }
    });

    function getRandomColor() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    //
    // var h=document.createElement('input');
    // var t=document.createTextNode('Hello World');
    // h.appendChild(t);
    // h.setAttributeNS(null, 'href', 'http://www.google.com');
    //
    // document.getElementById('canvas').appendChild(h);

</script>
</body>
</html>
