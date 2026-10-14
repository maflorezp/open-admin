# Changelog
See https://open-admin.org/docs/

Este fork (maflorezp/open-admin) parte de la v1.0.27 de open-admin-org. Cada cambio cita en su
commit el repositorio y el commit de origen.

## v1.0.29

### Correcciones
- Filtros de columna: `RangeFilter` e `InputFilter` escapan el valor de la URL (había XSS reflejado);
  `RangeFilter`, `InputFilter` y `CheckFilter` toleran valores mal formados (`?col=5`, `?col[]=x`);
  el rango acepta 0 como límite y el selector de fecha llega al campo «hasta».
- hasMany y table: una fila sin la bandera `_remove_` ya no da 500 al validar ni al guardar.
- Restaurar en lote: la detección de SoftDeletes usa el modelo original de la grilla.

### Novedades (todas opcionales; sin usarlas nada cambia)
- `admin.csrf_refresh` (minutos, por defecto 0): refresca el token CSRF de forma periódica.
  Ojo: mientras la pestaña esté abierta la sesión no vence.
- `Admin::disablePjax()`: la página se carga completa y sus enlaces no usan PJAX.
- `admin_flashjs()` y varios `admin_toastr()` a la vez; `admin.toastr_config` con opciones por defecto.
- Filtros del grid: `$filter->setCols()`, `->filter([...], 'radio')`, `->useNative()`, `->hideEmpty()`,
  `between()->setPlaceholders()` y `datetime(['icon' => ...])`.
- Acciones: `$force_response`, `title()` y `target()` en RowAction, `beforeRender()`, `cssClass()`,
  `$actions->getAction()`, y restaurar registros borrados con `enableRestore()` (fila y lote).
- ListField: Enter crea o salta de fila, Retroceso/Supr borra la fila vacía, pegar varias líneas crea filas.
- Widget `OpenAdmin\Admin\Widgets\Modal`.
- Login: `admin.login_layout` (`default`, `center-box`, `split-screen`), `login_subtitle`, `login_quote`,
  `login_quote_author`, `login_copyright`, `login_css` y `Admin::cssForLogin()`.

## v1.0.28

Correcciones tomadas de la rama `develop` de open-admin-org (nunca publicada) y de los forks
bulba-man/open-admin y ziixdk/admin, entre ellas: campos `->ignore()` que se vaciaban al guardar,
edición en línea que vaciaba otros campos o fallaba con `embeds`, filtros de columna que perdían los
demás filtros, acciones con campos que morían antes de ejecutarse y sin mostrar el error, dos
`load()`/`ajax()` en la misma página que tumbaban el JS, `load()` con padre múltiple o vacío, error de
borrado que se mostraba como éxito, Pjax con 500 ante errores sin excepción, `LA.token` tardío,
«Undefined index» en `sortable`, `_remove_` y vista previa de archivos, y el buscador del menú.
