@echo on 
echo .. 
echo Fetching Rhodes files 
cd\"Program Files (x86)\WinSCP\"

winscp.com /command ^
    "open ftpes://165.165.152.51/" ^
    username: rff/Alan.Argall^
    password: Pass@2468^
    "pwd" ^
    "exit"
