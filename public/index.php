<?php
include_once('../lib/Readability.php');
include_once('../lib/Pattern.php');

$text = 'Heavy metals are generally defined as metals with relatively high densities, atomic weights, or atomic numbers. The criteria used, and whether metalloids are included, vary depending on the author and context. In metallurgy, for example, a heavy metal may be defined on the basis of density, whereas in physics the distinguishing criterion might be atomic number, while a chemist would likely be more concerned with chemical behavior. More specific definitions have been published, but none of these have been widely accepted. The definitions surveyed in this article encompass up to 96 out of the 118 chemical elements; only mercury, lead and bismuth meet all of them.';

$readability = new Readability();
echo $readability->easeScore($text);
echo "\n";

$pattern = new Pattern();
echo "Printing size of the first of four pattern arrays: ";
echo sizeof($pattern->{'subtract_syllable_patterns'});

# What PHP version is this?
echo "\n";
echo 'Current PHP version: ' . phpversion();

