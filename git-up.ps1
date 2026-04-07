param (
    [Parameter(Mandatory=$true)]
    [string]$Mensaje
)

git add .
git commit -m "$Mensaje"
git push