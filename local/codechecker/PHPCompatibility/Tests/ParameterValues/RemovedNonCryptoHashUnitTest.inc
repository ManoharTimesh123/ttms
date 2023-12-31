<?php
/*
 * Test hash functions PHP 7.2 change in accepted values.
 */

// OK.
hash_init( 'fnv132');
hash_init( 'sha1', HASH_HMAC);
hash_init( algo: 'gost-crypto', 1);

// Not OK.
hash_hmac('adler32');
hash_hmac_file("crc32");
hash_pbkdf2('crc32b');
hash_init( flags: HASH_HMAC, algo: 'fnv132' );
hash_hmac('fnv1a32');
hash_hmac_file("fnv164");
hash_pbkdf2('fnv1a64');
hash_init( 'joaat', 1);
hash_pbkdf2(
    'adler32' // Comment.
);
hash_init( 'crc32b', HASH_HMAC /*comment*/);
