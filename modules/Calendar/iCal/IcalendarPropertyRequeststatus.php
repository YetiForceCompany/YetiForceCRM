<?php

class IcalendarPropertyRequeststatus extends IcalendarProperty
{
    // IMPORTANT NOTE: This property value includes TEXT fields
    // separated by semicolons. Unfortunately, auto-value-formatting
    // cannot be used in this case. As an exception, the value passed
    // to this property MUST be already escaped.

    public $name = 'REQUEST-STATUS';
    public $val_type = RFC2445_TYPE_TEXT;

    public function construct()
    {
        $this->valid_parameters = [
            'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL,
        ];
    }

    public function isValidValue($value)
    {
        if (!is_string($value) || empty($value)) {
            return false;
        }

        $len = strlen($value);
        $parts = [];
        $from = 0;
        $escch = false;

        for ($i = 0; $i < $len; ++$i) {
            if ($value{$i} == ';' && !$escch) {
                // Token completed
                $parts[] = substr($value, $from, $i - $from);
                $from = $i + 1;
                continue;
            }
            $escch = ($value{$i} == '\\');
        }
        // Add one last token with the remaining text; if the value
        // ended with a ';' it was illegal, so check that this token
        // is not the empty string.
        $parts[] = substr($value, $from);

        $count = count($parts);

        // May have 2 or 3 tokens (last one is optional)
        if ($count != 2 && $count != 3) {
            return false;
        }

        // REMEMBER: if ANY part is empty, we have an illegal value
        // First token must be hierarchical numeric status (3 levels max)
        if (strlen($parts[0]) == 0) {
            return false;
        }

        if ($parts[0]{0} < '1' || $parts[0]{0} > '4') {
            return false;
        }

        $len = strlen($parts[0]);

        // Max 3 levels, and can't end with a period
        if ($len > 5 || $parts[0]{$len - 1} == '.') {
            return false;
        }

        for ($i = 1; $i < $len; ++$i) {
            if (($i & 1) == 1 && $parts[0]{$i} != '.') {
                // Even-indexed chars must be periods
                return false;
            } elseif (($i & 1) == 0 && ($parts[0]{$i} < '0' || $parts[0]{$i} > '9')) {
                // Odd-indexed chars must be numbers
                return false;
            }
        }

        // Second and third tokens must be TEXT, and already escaped, so
        // they are not allowed to have UNESCAPED semicolons, commas, slashes,
        // or any newlines at all

        for ($i = 1; $i < $count; ++$i) {
            if (strpos($parts[$i], "\n") !== false) {
                return false;
            }

            $len = strlen($parts[$i]);
            if ($len == 0) {
                // Cannot be empty
                return false;
            }

            $parts[$i] .= '#'; // This guard token saves some conditionals in the loop

            for ($j = 0; $j < $len; ++$j) {
                $thischar = $parts[$i]{$j};
                $nextchar = $parts[$i]{$j + 1};
                if ($thischar == '\\') {
                    // Next char must now be one of ";,\nN"
                    if ($nextchar != ';' && $nextchar != ',' && $nextchar != '\\' &&
                        $nextchar != 'n' && $nextchar != 'N') {
                        return false;
                    }

                    // OK, this escaped sequence is correct, bypass next char
                    ++$j;
                    continue;
                }
                if ($thischar == ';' || $thischar == ',' || $thischar == '\\') {
                    // This wasn't escaped as it should
                    return false;
                }
            }
        }

        return true;
    }

    public function setValueICal($value)
    {
        // Must override this, otherwise the value would be quoted again
        if ($this->isValidValue($value)) {
            $this->value = $value;

            return true;
        }

        return false;
    }
}
