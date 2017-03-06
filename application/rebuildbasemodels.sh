#!/usr/bin/env bash

declare -A models
models["user"]="UserBase"
models["password_history"]="PasswordHistoryBase"

for i in "${!models[@]}"; do
    CMD="./yii gii/model --tableName=$i --modelClass=${models[$i]} --enableI18N=1 --overwrite=1 --interactive=0 --ns=\common\models"
    echo $CMD
    $CMD
done
