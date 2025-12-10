#!/bin/bash

# Цвета для вывода.
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color.

# Получаем путь к директории темы.
THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

cd "$THEME_DIR" || exit 1

# Имя архива.
ARCHIVE_NAME="fs-rocket-$(date +%Y%m%d-%H%M%S).zip"

# Папка для билдов.
BUILDS_DIR="$THEME_DIR/builds"

# Создаем папку builds если её нет.
if [ ! -d "$BUILDS_DIR" ]; then
    mkdir -p "$BUILDS_DIR"
fi

# Очищаем папку builds перед билдом.
echo -e "${YELLOW}Очистка папки builds...${NC}"
rm -rf "$BUILDS_DIR"/*

# Проверяем наличие pnpm.
if ! command -v pnpm &> /dev/null; then
    echo -e "${RED}Ошибка: pnpm не найден. Установите pnpm: npm install -g pnpm${NC}"
    exit 1
fi

# Определяем команду composer (глобальный или локальный).
if command -v composer &> /dev/null; then
    COMPOSER_CMD="composer"
else
    COMPOSER_CMD="$THEME_DIR/vendor/bin/composer"
    if [ ! -f "$COMPOSER_CMD" ]; then
        echo -e "${RED}Ошибка: composer не найден${NC}"
        exit 1
    fi
fi

echo -e "${GREEN}Начинаем сборку темы...${NC}"

# Шаг 1: Установка всех pnpm зависимостей (нужны dev для сборки).
echo -e "${YELLOW}Установка pnpm зависимостей...${NC}"
if ! pnpm install; then
    echo -e "${RED}Ошибка при установке pnpm зависимостей${NC}"
    exit 1
fi

# Шаг 2: Сборка проекта.
echo -e "${YELLOW}Сборка проекта...${NC}"
if ! pnpm build; then
    echo -e "${RED}Ошибка при сборке проекта${NC}"
    exit 1
fi

# Шаг 3: Установка composer зависимостей в режиме --no-dev.
echo -e "${YELLOW}Установка composer зависимостей (--no-dev)...${NC}"
# Удаляем vendor/bin/composer, так как он будет ссылаться на несуществующий файл после удаления dev-зависимостей.
if [ -f "$THEME_DIR/vendor/bin/composer" ]; then
    rm -f "$THEME_DIR/vendor/bin/composer"
fi
if ! $COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction; then
    echo -e "${RED}Ошибка при установке composer зависимостей${NC}"
    # Пытаемся восстановить для следующего запуска.
    $COMPOSER_CMD install --no-interaction 2>/dev/null || true
    exit 1
fi

# Шаг 4: Создание архива с исключениями.
echo -e "${YELLOW}Создание архива...${NC}"

# Получаем имя папки темы и переходим в родительскую директорию.
THEME_FOLDER_NAME=$(basename "$THEME_DIR")
PARENT_DIR=$(dirname "$THEME_DIR")

cd "$PARENT_DIR" || exit 1

# Создаем архив, исключая ненужные файлы и директории.
if ! zip -r "$BUILDS_DIR/$ARCHIVE_NAME" "fs-rocket" \
    -x "fs-rocket/node_modules/*" \
    -x "fs-rocket/src/*" \
    -x "fs-rocket/.scratch/*" \
    -x "fs-rocket/docs/*" \
    -x "fs-rocket/scripts/*" \
    -x "fs-rocket/builds/*" \
    -x "fs-rocket/bin/*" \
    -x "fs-rocket/vendor/bin/*" \
    -x "fs-rocket/.git/*" \
    -x "fs-rocket/.vscode/*" \
    -x "fs-rocket/.idea/*" \
    -x "fs-rocket/.env*" \
    -x "fs-rocket/.hot" \
    -x "fs-rocket/.gitignore" \
    -x "fs-rocket/.gitattributes" \
    -x "fs-rocket/pnpm-lock.yaml" \
    -x "fs-rocket/package.json" \
    -x "fs-rocket/tsconfig.json" \
    -x "fs-rocket/vite.config.ts" \
    -x "fs-rocket/postcss.config.cjs" \
    -x "fs-rocket/pnpm-workspace.yaml" \
    -x "fs-rocket/*.code-workspace" \
    -x "fs-rocket/*.log" \
    -x "fs-rocket/phpstan.neon" \
    -x "fs-rocket/ruleset.xml" \
    -x "fs-rocket/.DS_Store" \
    -q; then
    echo -e "${RED}Ошибка при создании архива${NC}"
    # Восстанавливаем dev зависимости даже при ошибке.
    echo -e "${YELLOW}Восстановление dev зависимостей...${NC}"
    cd "$THEME_DIR" || exit 1
    $COMPOSER_CMD install --no-interaction 2>/dev/null || true
    exit 1
fi

# Возвращаемся в директорию темы.
cd "$THEME_DIR" || exit 1

# Шаг 5: Восстановление dev зависимостей composer.
echo -e "${YELLOW}Восстановление dev зависимостей composer...${NC}"
if ! $COMPOSER_CMD install --no-interaction; then
    echo -e "${YELLOW}Предупреждение: не удалось восстановить dev зависимости${NC}"
fi

echo -e "${GREEN}Сборка завершена успешно!${NC}"
echo -e "${GREEN}Архив создан: ${ARCHIVE_NAME}${NC}"
echo -e "${GREEN}Расположение: $BUILDS_DIR/$ARCHIVE_NAME${NC}"

# Показываем размер архива.
ARCHIVE_SIZE=$(du -h "$BUILDS_DIR/$ARCHIVE_NAME" | cut -f1)
echo -e "${GREEN}Размер архива: ${ARCHIVE_SIZE}${NC}"

