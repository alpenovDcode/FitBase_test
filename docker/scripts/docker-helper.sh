#!/bin/bash

# Цвета для текста
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Функция для вывода справки
function show_help {
    echo -e "${BLUE}Скрипт для управления Docker-контейнерами проекта FitBase${NC}"
    echo ""
    echo "Использование:"
    echo "  $0 [команда]"
    echo ""
    echo "Команды:"
    echo "  start       - Запустить все контейнеры"
    echo "  stop        - Остановить все контейнеры"
    echo "  restart     - Перезапустить все контейнеры"
    echo "  status      - Показать статус контейнеров"
    echo "  logs        - Показать логи всех контейнеров"
    echo "  migrate     - Выполнить миграции базы данных"
    echo "  bash        - Запустить bash в контейнере frontend"
    echo "  prune       - Очистить неиспользуемые Docker-ресурсы"
    echo "  help        - Показать эту справку"
    echo ""
}

# Функция для проверки наличия docker-compose
function check_docker_compose {
    if ! command -v docker-compose &> /dev/null; then
        echo -e "${RED}Ошибка: docker-compose не установлен.${NC}"
        exit 1
    fi
}

# Основная логика скрипта
case "$1" in
    start)
        echo -e "${GREEN}Запуск контейнеров...${NC}"
        docker-compose up -d
        echo -e "${GREEN}Контейнеры запущены.${NC}"
        ;;
    stop)
        echo -e "${YELLOW}Остановка контейнеров...${NC}"
        docker-compose down
        echo -e "${YELLOW}Контейнеры остановлены.${NC}"
        ;;
    restart)
        echo -e "${YELLOW}Перезапуск контейнеров...${NC}"
        docker-compose restart
        echo -e "${GREEN}Контейнеры перезапущены.${NC}"
        ;;
    status)
        echo -e "${BLUE}Статус контейнеров:${NC}"
        docker-compose ps
        ;;
    logs)
        echo -e "${BLUE}Логи контейнеров:${NC}"
        docker-compose logs -f
        ;;
    migrate)
        echo -e "${GREEN}Выполнение миграций...${NC}"
        docker-compose exec frontend php yii migrate --interactive=0
        echo -e "${GREEN}Миграции выполнены.${NC}"
        ;;
    bash)
        echo -e "${BLUE}Запуск bash в контейнере frontend...${NC}"
        docker-compose exec frontend bash
        ;;
    prune)
        echo -e "${YELLOW}Очистка неиспользуемых Docker-ресурсов...${NC}"
        docker system prune -f
        echo -e "${GREEN}Очистка завершена.${NC}"
        ;;
    help)
        show_help
        ;;
    *)
        echo -e "${RED}Неизвестная команда: $1${NC}"
        show_help
        exit 1
        ;;
esac

exit 0 