<?php namespace x;

function linkify($content) {
    if (!$content) {
        return $content;
    }
    $out = "";
    foreach (\preg_split('/(' . \implode('|', [
        // Processing instruction
        '<\?(?:"[^"]*"|\'[^\']*\'|[^>?])*\?>',
        // Comment
        '<\!--[\s\S]*?-->',
        // Character data section
        '<\!\[CDATA\[[\s\S]*?\]\]>',
        // Document type
        '<\!(?:"[^"]*"|\'[^\']*\'|[^>])*>',
        // Anchor element
        '<a(?:\s(?:"[^"]*"|\'[^\']*\'|[^>])*)?>[\s\S]*?<\/a>',
        // Any closing element
        '<\/(?:[^\s"\'\/<=>]+)>',
        // Any opening element and void element
        '<(?:[^\s"\'\/<=>]+)(?:\s(?:"[^"]*"|\'[^\']*\'|[^>])*)?>',
        // Maybe a plain text link
        "'https?:\/\/\S+'",
        '"https?:\/\/\S+"',
        '\(https?:\/\/\S+\)',
        '\<https?:\/\/\S+\>',
        '\[https?:\/\/\S+\]',
        '\{https?:\/\/\S+\}',
        'https?:\/\/\S+'
    ]) . ')/', $content, null, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY)) {
        // TODO
    }
    return $out;
}

\Hook::set('page.content', __NAMESPACE__ . "\\linkify", 2.1);