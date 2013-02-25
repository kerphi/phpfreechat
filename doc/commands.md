# Chat commands

Here is a list of commands that can be used when chatting.

## op

Add the operator flag on the given user and channel.  You have to be op to use this command.
Thanks to your operator rights on a given channel, you can moderate this channel by using the kick and ban commands on other joined users. 

```
/op ["#channel"] "<username>"
```

Available in phpFreeChat ≥ 2.1.0

## deop

Removes the operator flag on the given user and channel. You have to be op to use this command.

```
/deop ["#channel"] "<username>"
```

Available in phpFreeChat ≥ 2.1.0

## kick

Takes off a user from a given channel. You have to be op to use this command.
This is a first tool to moderate the channel. Have a look to the ban command for a more serious sanction.

```
/kick ["#channel"] "<username>" ["<reason>"]
```

Available in phpFreeChat ≥ 2.1.0


## ban

Add a user to channel's bannished list. You have to be op to use this command.
When a user is added to this list, he cannot join later as long as he is in the list. Use this command to moderate the channel with serious sanction. 

```
/ban ["#channel"] "<username>" ["<reason>"]
```

Available in phpFreeChat ≥ 2.1.0

## kickban

Same as the /ban command but the user is directly kicked from the channel. You have to be op to use this command.

```
/kickban ["#channel"] "<username>" ["<reason>"]
```

Available in phpFreeChat ≥ 2.1.0

## unban

Remove a user from the channel's bannished list. You have to be op to use this command.
Notice: the user is not notified he has been unbanned.

```
/unban ["#channel"] "<username>"
```

Available in phpFreeChat ≥ 2.1.0

## banlist

Lists username in the channel's banished list. You have to be on the channel to use this command.

```
/banlist ["#channel"]
```

Available in phpFreeChat ≥ 2.1.0
