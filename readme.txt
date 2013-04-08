=== PHP PIN Number Application ==

This application generates Random PIN numbers that satisfy two criteria:

1) Each integer in the PIN is used no more than twice
2) Once a PIN number is generated it can't be used again

Technology:

1) PHP 5.4 with pdo_mysql extension loaded
2) MySQL 5.5

How it works:

1) All used PIN Numbers are Queried from the database and populated into an array. This array will be used later to check our new number against. Using an array like this prevents costly revisits to the database further down the script.

2) A Random PIN Number is generated.

3) The Random PIN Number is checked against itself to make sure that no integer is used more than twice. For example, 3533 would be unacceptable. Once an acceptable Random PIN Number is generated the script moves on.

4) The Random PIN Number is now checked against the array of PIN Numbers. If a match is found in the array the check stops and the script returns to stage 2. If no match is found against the array then the Random PIN Number becomes our PIN Number.

5) The PIN Number is added to the database to prevent it being used again.

6) The PIN Number is presented.
