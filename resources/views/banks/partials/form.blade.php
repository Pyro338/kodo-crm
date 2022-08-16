<table class="table table-striped text-left">
    <tr>
        <td>
            <label for="place">Место на 01.08.2016</label>
        </td>
        <td>
            <input type="number" class="form-control" name="place" value="{{$bank->place or ""}}" required placeholder="Место на 01.08.2016">
        </td>
    </tr>
    <tr>
        <td>
            <label for="reg_number">Рег. номер</label>
        </td>
        <td>
            <input type="number" class="form-control" name="reg_number" value="{{$bank->reg_number or ""}}" required placeholder="Рег. номер">
        </td>
    </tr>
    <tr>
        <td>
            <label for="name">Наименование банка</label>
        </td>
        <td>
            <input type="text" class="form-control" name="name" value="{{$bank->name or ""}}" required placeholder="Наименование банка">
        </td>
    </tr>
    <tr>
        <td>
            <label for="city">Город</label>
        </td>
        <td>
            <input type="text" class="form-control" name="city" value="{{$bank->city or ""}}" required placeholder="Город">
        </td>
    </tr>
    <tr>
        <td>
            <label for="place_active">Место по активам на 01.08.2016</label>
        </td>
        <td>
            <input type="text" class="form-control" name="place_active" value="{{$bank->place_active or ""}}" required
                   placeholder="Место по активам на 01.08.2016">
        </td>
    </tr>
    <tr>
        <td>
            <label for="credits">Кредиты физ. лицам, всего на 01.08.2016, млн. руб.</label>
        </td>
        <td>
            <input type="text" class="form-control" name="credits" value="{{$bank->credits or ""}}" required
                   placeholder="Кредиты физ. лицам, всего на 01.08.2016, млн. руб.">
        </td>
    </tr>
    <tr>
        <td>
            <label for="license">Лицензия</label>
        </td>
        <td>
            <input type="text" class="form-control" name="license" value="{{$bank->license or ""}}" placeholder="Лицензия">
        </td>
    </tr>
    <tr>
        <td>
            <label for="license_status">Статус лицензии</label>
        </td>
        <td>
            <input type="checkbox" class="checkbox" name="license_status" value="1"
                   @if(isset($bank->license_status) && $bank->license_status == 1)checked="checked"@endif>
        </td>
    </tr>
    <tr>
        <td>
            <label for="contacts">Адрес. Контакты</label>
        </td>
        <td>
            <input type="text" class="form-control" name="contacts" value="{{$bank->contacts or ""}}" placeholder="Адрес. Контакты">
        </td>
    </tr>
    <tr>
        <td>
            <label for="comments">Комментарии</label>
        </td>
        <td>
            <input type="text" class="form-control" name="comments" value="{{$bank->comments or ""}}" placeholder="Комментарии">
        </td>
    </tr>
</table>
<a href="{{route('banks.index')}}" class="btn btn-primary">Вернуться</a><input type="submit" class="btn btn-primary" name="submit" value="Сохранить">