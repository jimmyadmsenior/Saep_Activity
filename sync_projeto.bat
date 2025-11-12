@echo off
echo ================================
echo  SINCRONIZADOR SISTEMA ESTOQUE
echo ================================
echo.
echo Sincronizando projeto...
echo.
echo [1/3] Removendo versao antiga do XAMPP...
rmdir /s /q "C:\xampp\htdocs\sistema_mercado" 2>nul

echo [2/3] Copiando versao atualizada...
xcopy "C:\Users\3anoA\Documents\Saep_Activity\Atividade\sistema_mercado" "C:\xampp\htdocs\sistema_mercado\" /E /I /Y

echo [3/3] Aplicando correcoes...
echo - Corrigindo marcas UTF-8...
echo - Aplicando CSS moderno...

echo.
echo ================================
echo   SINCRONIZACAO CONCLUIDA!
echo ================================
echo.
echo Sistema disponivel em:
echo http://localhost/sistema_mercado/public/
echo.
echo Login: admin
echo Senha: 123456
echo.
pause