<?php

// ============================================================================
// File:    main.php
// Author:  Recep Seymen Konuk <konukrecepseymen@gmail.com>
//
// Licensed under the terms of the LICENSE file in the project root directory.
// ============================================================================

// ============================================================================
//  NOT: Bu uygulama eğitim amaçlı geliştirilmiştir.
//  UTF-8 kodlamasının temel byte yapısını incelemeyi amaçlar.
//  Tam kapsamlı bir UTF-8 doğrulaması yapmaz ve tüm geçersiz
//  kodlama durumlarını doğru şekilde ele almayı garanti etmez.
// ============================================================================

function utf8_is_continuation_byte(int $byte): bool
{
    return ($byte & 0b11000000) === 0b10000000;
}

function utf8_sequence_length(int $first_byte): int
{
    // 11110XXX ile başlıyorsa
    if (($first_byte & 0b11111000) === 0b11110000) {
        return 4;
    }
    // 1110XXXX ile başlıyorsa
    elseif (($first_byte & 0b11110000) === 0b11100000) {
        return 3;
    }
    // 110XXXXX ile başlıyorsa
    elseif (($first_byte & 0b11100000) === 0b11000000) {
        return 2;
    }

    return 1;
}

function utf8_is_valid_character(string $text, int $start_index, int $sequence_length): bool
{
    if ($start_index + $sequence_length > strlen($text)) {
        return false;
    }

    for ($i = 1; $i < $sequence_length; $i++) {
        $continuation_byte = ord($text[$start_index + $i]);
        if (!utf8_is_continuation_byte($continuation_byte)) {
            return false;
        }
    }

    return true;
}

function utf8_character_length(string $text, int $start_index): int
{
    $first_byte = ord($text[$start_index]);
    $sequence_length = utf8_sequence_length($first_byte);

    if (utf8_is_valid_character($text, $start_index, $sequence_length)) {
        return $sequence_length;
    } else {
        return 1;
    }
}

function utf8_character(string $text, int $start_index): string
{
    $character = "";

    $character_length = utf8_character_length($text, $start_index);
    for ($i = 0; $i < $character_length; $i++) {
        $character .= $text[$start_index + $i];
    }

    return $character;
}

/** @return array<string> */
function utf8_characters(string $text): array
{
    $characters = [];

    $i = 0;
    while ($i < strlen($text)) {
        $character = utf8_character($text, $i);
        $characters[] = $character;
        $i += strlen($character);
    }

    return $characters;
}

function utf8_reverse(string $text): string
{
    $reversed = "";

    $characters = utf8_characters($text);
    for ($i = count($characters) - 1; $i >= 0; $i--) {
        $character = $characters[$i];
        $reversed .= $character;
    }

    return $reversed;
}

$text = "Çay";
echo utf8_reverse($text);
