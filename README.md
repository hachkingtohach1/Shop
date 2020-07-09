# Shop
One plugin for economy server PMMP (GUI).

# How to setup ?

- Look at the bottom of the #config you'll see zero count as your shop page and the values inside are in order according to your customization.
'''ID | Meta | Name Item | Lore Item | location | Money | Count Item | EnchantMent | Lore Result'''

# Config

```
items:
        # You can add Catetory any, Example: this is count 0 -> 3 you can create one catetory with 4 and up !
    0: 
        name: "Tool"
        items:
                        #    ID | Meta | Name Item | Lore Item | location | Money | Count Item | EnchantMent | Lore Result
            - [12, 0, '§l§aSand_1', ['Money: %money', 'Message'], 1, 1000, 5, [[9, 1, "CE"]], ['ABC', 'ABCD']]
            - [12, 0, '§l§aSand_2', ['Money: %money', 'Message'], 5, 1000, 5, [["haste", 1, "CE"]], ['ABC', 'ABCD']]
 ```
