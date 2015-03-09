<?php
function smarty_modifier_bitwise_and($value1, $value2, $fromBase = 16, $toBase = 16) {
    return Util::bitwiseAnd($value1, $value2, $fromBase, $toBase);
}