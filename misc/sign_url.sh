#!/bin/zsh

url=$1

# sha256(tokenid|token|create|expire)

time=$(date +%s)
expire=$(($time + 60))
nonce=$(openssl rand -hex 64)
signature=$(printf "$ID|$TOKEN|$time|$expire|$nonce" | shasum -a 256 | cut -d ' ' -f 1)
link=$(printf $url\?id=$ID\&time=$time\&expires=$expire\&signature=$signature\&nonce=$nonce)
printf "Opening Link...\n"
open $link
