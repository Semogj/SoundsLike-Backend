<?php
echo TRUE ? 'a' : TRUE ? 'b' : 'c';
echo '<br />';
echo TRUE ? 'a' : (TRUE ? 'b' : 'c');
echo '<br />';
echo TRUE ? 'a' : TRUE ? 'b' : TRUE ? 'c' : 'd';
echo '<br />';
echo TRUE ? 'a' : TRUE ? 'b' : (TRUE ? 'c' : 'd');

?>
