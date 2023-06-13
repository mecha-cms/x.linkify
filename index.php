<?php namespace x\linkify;

function page__content($content) {
    if (!$content) {
        return $content;
    }
    $pattern = '/# Rev:20100913_0900 github.com\/jmrware\/LinkifyURL
    # Match http & ftp URL that is not already linkified.
      # Alternative 1: URL delimited by (parentheses).
      (\()                     # $1  "(" start delimiter.
      ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+)  # $2: URL.
      (\))                     # $3: ")" end delimiter.
    | # Alternative 2: URL delimited by [square brackets].
      (\[)                     # $4: "[" start delimiter.
      ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+)  # $5: URL.
      (\])                     # $6: "]" end delimiter.
    | # Alternative 3: URL delimited by {curly braces}.
      (\{)                     # $7: "{" start delimiter.
      ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+)  # $8: URL.
      (\})                     # $9: "}" end delimiter.
    | # Alternative 4: URL delimited by <angle brackets>.
      (<|&(?:lt|\#60|\#x3c);)  # $10: "<" start delimiter (or HTML entity).
      ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+)  # $11: URL.
      (>|&(?:gt|\#62|\#x3e);)  # $12: ">" end delimiter (or HTML entity).
    | # Alternative 5: URL not delimited by (), [], {} or <>.
      (                        # $13: Prefix proving URL not already linked.
        (?: ^                  # Can be a beginning of line or string, or
        | [^=\s\'"\]]          # a non-"=", non-quote, non-"]", followed by
        ) \s*[\'"]?            # optional whitespace and optional quote;
      | [^=\s]\s+              # or... a non-equals sign followed by whitespace.
      )                        # End $13. Non-prelinkified-proof prefix.
      ( \b                     # $14: Other non-delimited URL.
        (?:ht|f)tps?:\/\/      # Required literal http, https, ftp or ftps prefix.
        [a-z0-9\-._~!$\'()*+,;=:\/?#[\]@%]+ # All URI chars except "&" (normal*).
        (?:                    # Either on a "&" or at the end of URI.
          (?!                  # Allow a "&" char only if not start of an...
            &(?:gt|\#0*62|\#x0*3e);                  # HTML ">" entity, or
          | &(?:amp|apos|quot|\#0*3[49]|\#x0*2[27]); # a [&\'"] entity if
            [.!&\',:?;]?        # followed by optional punctuation then
            (?:[^a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]|$)  # a non-URI char or EOS.
          ) &                  # If neg-assertion true, match "&" (special).
          [a-z0-9\-._~!$\'()*+,;=:\/?#[\]@%]* # More non-& URI chars (normal*).
        )*                     # Unroll-the-loop (special normal*)*.
        [a-z0-9\-_~$()*+=\/#[\]@%]  # Last char can\'t be [.!&\',;:?]
      )                        # End $14. Other non-delimited URL.
    /imx';
    $parts = \preg_split('/(' . \implode('|', [
        // Processing instruction
        '<\?(?:"[^"]*"|\'[^\']*\'|[^>?])*\?>',
        // Comment
        '<\!--[\s\S]*?-->',
        // Character data section
        '<\!\[CDATA\[[\s\S]*?\]\]>',
        // Document type
        '<\!(?:"[^"]*"|\'[^\']*\'|[^>])*>',
        // Anchor element
        '<a(?:\s(?:"[^"]*"|\'[^\']*\'|[^\/>])*)?>[\s\S]*?<\/a>',
        // Script element
        '<script(?:\s(?:"[^"]*"|\'[^\']*\'|[^\/>])*)?>[\s\S]*?<\/script>',
        // Style element
        '<style(?:\s(?:"[^"]*"|\'[^\']*\'|[^\/>])*)?>[\s\S]*?<\/style>',
        // Text area element
        '<textarea(?:\s(?:"[^"]*"|\'[^\']*\'|[^\/>])*)?>[\s\S]*?<\/textarea>',
        // Closing element
        '<\/(?:[^\s"\'\/<=>]+)>',
        // Opening and void element
        '<(?:[^\s"\'\/<=>]+)(?:\s(?:"[^"]*"|\'[^\']*\'|[^>])*)?>',
        // Character entity
        '&(?:[a-z\d]+|#\d+|#x[a-f\d]+);'
    ]) . ')/i', $content, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY);
    $content = "";
    foreach ($parts as $part) {
        if ($part && (
            '<' === $part[0] && '>' === \substr($part, -1) ||
            '&' === $part[0] && ';' === \substr($part, -1)
        )) {
            $content .= $part;
            continue;
        }
        $content .= \preg_replace_callback($pattern, static function ($m) {
            $m = \array_replace(\array_fill(0, 15, ""), $m);
            $host = $_SERVER['HTTP_HOST'];
            $u = $m[2] . $m[5] . $m[8] . $m[11] . $m[14];
            if (
                false === \strpos($u, '://' . $host . '/') &&
                false === \strpos($u, '://' . $host . '?') &&
                false === \strpos($u, '://' . $host . '&') &&
                false === \strpos($u, '://' . $host . '#') &&
                false === \strpos($u . \P, '://' . $host . \P)
            ) {
                $x = ' rel="nofollow" target="_blank"';
            } else {
                $x = "";
            }
            return $m[1] . $m[4] . $m[7] . $m[10] . $m[13] . '<a href="' . $u . '"' . $x . '>' . $m[2] . $m[5] . $m[8] . $m[11] . $m[14] . '</a>' . $m[3] . $m[6] . $m[9] . $m[12];
        }, $part);
    }
    return $content;
}

\Hook::set('page.content', __NAMESPACE__ . "\\page__content", 2.1);

if (\defined("\\TEST") && 'x.linkify' === \TEST && \is_file($test = __DIR__ . \D . 'test.php')) {
    require $test;
}