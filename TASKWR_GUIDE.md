# Guía rápida Taskwarrior — proyecto faristol

## Comandos esenciales

```bash
# Agregar tarea
task add +backend "Implementar caché"

# Listar pendientes
task list

# Filtrar por etiqueta
task +urgent list
task +flutter list

# Marcar completada (usa el ID)
task 5 done

# Editar descripción
task 5 edit

# Borrar (no preguntas)
task 5 delete

# Ver tarea individual
task 5 info
```

## TUI (interfaz visual)

```bash
taskwarrior-tui
```

Vim-keys: `j/k` navegar, `Enter` abrir, `a` agregar, `d` done, `q` salir, `/` buscar, `F1` ayuda.

## Filtros útiles

| Qué quieres | Comando |
|---|---|
| Pendientes | `task pending` |
| Urgentes | `task +urgent` |
| Por área | `task +backend`, `task +frontend` |
| Preguntas pendientes | `task +urgent` |
| QA | `task +qa` |
| Flutter | `task +flutter` |
| Reporte semanal | `task burndown.weekly` |
| Resumen por tags | `task summary` |

## Sinónimos

`task` = `t` (si configuras alias en bashrc: `alias t=task`)
