# Shop 🛒
One plugin for economy server PMMP (GUI).
Lib: Invmenu, CustomEnchantment, EconomyAPI 

- Invmenu: https://poggit.pmmp.io/ci/Muqsit/InvMenu/InvMenu
- CustomEnchantment: https://poggit.pmmp.io/p/PiggyCustomEnchants/2.3.0
- EconomyAPI: https://poggit.pmmp.io/p/EconomyAPI/5.7.2

# How to setup ?
- Look at the bottom of the #config you'll see zero count as your shop page and the values inside are in order according to your customization.
```ID | Meta | Name Item | Lore Item | location | Money | Count Item | EnchantMent | Lore Result```
- To create 1 new page you need to know that the largest number of pages you do is page number and get that page number 1
- Example: 
```I need to create a page but the page number I'm having is 5 pages but the page number starts from 0-> 4, I'll take the largest page number I'm having and create a new one```

```
    5: 
        name: "Testing"
        items:
            #  ID | Meta | Name Item | Lore Item | location | Money | Count Item | EnchantMent | Lore Result
            - [1, 0, 'Name', ['Money: %money', 'How much?'], 1, 1000, 5, [[9, 1, "EC"]], ['ABC', 'ABCD']]
            - [1, 0, 'Name', ['Money: %money', 'How much?'], 2, 1000, 5, false, ['ABC', 'ABCD']]
```
- You can see the enchant and custom enchat sections if you don't need it, you can leave false.

# Config

```
items:
        # You can add Catetory any, Example: this is count 0 -> 3 you can create one catetory with 4 and up !
    0: 
        name: "Tool"
        items:
             # ID | Meta | Name Item | Lore Item | location | Money | Count Item | EnchantMent | Lore Result
            - [12, 0, '§l§aSand_1', ['Money: %money', 'Message'], 1, 1000, 5, [[9, 1, "CE"]], ['ABC', 'ABCD']]
            - [12, 0, '§l§aSand_2', ['Money: %money', 'Message'], 5, 1000, 5, [["haste", 1, "CE"]], ['ABC', 'ABCD']]          
 ```
 
 # Update ?
 - I think, i will make custom setting for it! 😁
 
 # Ending
- Thanks for downloading!
