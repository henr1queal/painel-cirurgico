echo "--- INICIANDO DASHBOARD HOSPITALAR ---"

docker-compose up -d

echo "Aguardando serviços iniciarem..."
sleep 5

echo "Abrindo navegador..."
if [[ "$OSTYPE" == "darwin"* ]]; then
  open "http://localhost:9131"
else
  xdg-open "http://localhost:9131"
fi