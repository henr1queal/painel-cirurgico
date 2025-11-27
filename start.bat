@echo off
echo --- INICIANDO DASHBOARD HOSPITALAR ---

docker-compose up -d

echo Aguardando servicos iniciarem...
timeout /t 5 >nul

echo Abrindo o painel...
start http://localhost:9131