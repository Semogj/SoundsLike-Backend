<?php
namespace VIRUS\webservice;
/**
 * Check if the parsed value is a valid positive integer, and returns a default value if not.
 * The value 0 is counted as invalid. 
 * @param type $theValue
 * @param type $theDefault
 * @param type $base the integer base, default is base 10. 
 */
function validate_pos_int($theValue, $theDefault, $base = 10) {
    $theValue = intval($theValue, $base);
    return $theValue > 0 ? $theValue : $theDefault;
}

function is_valid_url($theString) {
    return preg_match(
    '/^
(# Scheme
 [a-z][a-z0-9+\-.]*:
 (# Authority & path
  \/\/
  ([a-z0-9\-._~%!$&\'()*+,;=]+@)?              # User
  ([a-z0-9\-._~%]+                            # Named host
  |\[[a-f0-9:.]+\]                            # IPv6 host
  |\[v[a-f0-9][a-z0-9\-._~%!$&\'()*+,;=:]+\])  # IPvFuture host
  (:[0-9]+)?                                  # Port
  (\/[a-z0-9\-._~%!$&\'()*+,;=:@]+)*\/?          # Path
 |# Path without authority
  (\/?[a-z0-9\-._~%!$&\'()*+,;=:@]+(\/[a-z0-9\-._~%!$&\'()*+,;=:@]+)*\/?)?
 )
)
# Query
(\?[a-z0-9\-._~%!$&\'()*+,;=:@\/?]*)?
# Fragment
(\#[a-z0-9\-._~%!$&\'()*+,;=:@\/?]*)?
$/ix',
    $theString);
}


