<div class="table-responsive table-custom receiver-wrap">
   <table class="table table-borderd border">

       <tr class="form-group">
            <th>配送先氏名<em>必須</em></th>
              <td>
               <input type="text" class="form-control col-md-12{{ $errors->has('receiver.name') ? ' is-invalid' : '' }}" name="receiver[name]" value="{{ Ctm::isOld() ? old('receiver.name') : (Session::has('all.data.receiver') ? session('all.data.receiver.name') : '') }}" placeholder="例）山田太郎">
              
               @if ($errors->has('receiver.name'))
                   <div class="help-block text-danger receiver-error">
                       <span class="fa fa-exclamation form-control-feedback"></span>
                       <span>{{ $errors->first('receiver.name') }}</span>
                   </div>
               @endif
           </td>
        </tr>
     
         <tr class="form-group">
            <th>配送先フリガナ<em>必須</em></th>
              <td>
               <input type="text" class="form-control col-md-12{{ $errors->has('receiver.hurigana') ? ' is-invalid' : '' }}" name="receiver[hurigana]" value="{{ Ctm::isOld() ? old('receiver.hurigana') : (Session::has('all.data.receiver') ? session('all.data.receiver.hurigana') : '') }}" placeholder="例）ヤマダタロウ">
               
               @if ($errors->has('receiver.hurigana'))
                   <div class="help-block text-danger receiver-error">
                       <span class="fa fa-exclamation form-control-feedback"></span>
                       <span>{{ $errors->first('receiver.hurigana') }}</span>
                   </div>
               @endif
           </td>
        </tr>
        
        <tr class="form-group">
            <th>配送先電話番号<em>必須</em>
               {{-- <small>例）09012345678ハイフンなし半角数字</small> --}}
            </th>
              <td>
               <input type="text" class="form-control col-md-12{{ $errors->has('receiver.tel_num') ? ' is-invalid' : '' }}" name="receiver[tel_num]" value="{{ Ctm::isOld() ? old('receiver.tel_num') : (Session::has('all.data.receiver') ? session('all.data.receiver.tel_num') : '') }}" placeholder="例）09012345678（ハイフンなし半角数字）">
               
               @if ($errors->has('receiver.tel_num'))
                   <div class="help-block text-danger receiver-error">
                       <span class="fa fa-exclamation form-control-feedback"></span>
                       <span>{{ $errors->first('receiver.tel_num') }}</span>
                   </div>
               @endif
           </td>
        </tr>
        
        
        
        <tr class="form-group">
            <th>配送先郵便番号<em>必須</em>
               {{-- <small>例）1234567ハイフンなし半角数字</small> --}}
            </th>
              <td>
               <input id="zipcode_2" type="text" class="form-control col-md-12{{ $errors->has('receiver.post_num') ? ' is-invalid' : '' }}" name="receiver[post_num]" value="{{ Ctm::isOld() ? old('receiver.post_num') : (Session::has('all.data.receiver') ? session('all.data.receiver.post_num') : '') }}" placeholder="例）1234567（ハイフンなし半角数字）">
               
               @if ($errors->has('receiver.post_num'))
                   <div class="help-block help-block text-danger receiver-error">
                       <span class="fa fa-exclamation form-control-feedback"></span>
                       <span>{{ $errors->first('receiver.post_num') }}</span>
                   </div>
               @endif
           </td>
        </tr>
        
        <tr class="form-group">
           <th>配送先都道府県<em>必須</em></th>
           <td>
               <div class="select-wrap col-md-12 p-0">
                   <select id="pref_2" class="form-control select-first {{ $errors->has('receiver.prefecture') ? ' is-invalid' : '' }}" name="receiver[prefecture]">
                       <option disabled selected>選択して下さい</option>

                       @foreach($prefs as $pref)
                           <?php
                               $selected = '';
                               if(Ctm::isOld()) {
                                   if(old('receiver.prefecture') == $pref->name)
                                       $selected = ' selected';
                               }
                               else {
                                   if(Session::has('all.data.receiver') && session('all.data.receiver.prefecture') == $pref->name) {
                                       $selected = ' selected';
                                   }
                               }
                           ?>
                           <option value="{{ $pref->name }}"{{ $selected }}>{{ $pref->name }}</option>
                       @endforeach
                   </select>
                </div>
               
               @if ($errors->has('receiver.prefecture'))
                   <div class="help-block text-danger receiver-error">
                       <span class="fa fa-exclamation form-control-feedback"></span>
                       <span>{{ $errors->first('receiver.prefecture') }}</span>
                   </div>
               @endif
               
           </td>
        </tr>
        
        <tr class="form-group">
            <th>配送先住所1<small>（都市区それ以降）</small><em>必須</em></th>
              <td>
               <input id="address_2" type="text" class="form-control col-md-12{{ $errors->has('receiver.address_1') ? ' is-invalid' : '' }}" name="receiver[address_1]" value="{{ Ctm::isOld() ? old('receiver.address_1') : (Session::has('all.data.receiver') ? session('all.data.receiver.address_1') : '') }}" placeholder="例）小美玉市下吉影1-1">
               
               @if ($errors->has('receiver.address_1'))
                   <div class="help-block text-danger receiver-error">
                       <span class="fa fa-exclamation form-control-feedback"></span>
                       <span>{{ $errors->first('receiver.address_1') }}</span>
                   </div>
               @endif
           </td>
        </tr>
        
        <tr class="form-group">
            <th>配送先住所2<small>（建物/マンション名等）</small></th>
              <td>
               <input type="text" class="form-control col-md-12{{ $errors->has('receiver.address_2') ? ' is-invalid' : '' }}" name="receiver[address_2]" value="{{ Ctm::isOld() ? old('receiver.address_2') : (Session::has('all.data.receiver') ? session('all.data.receiver.address_2') : '') }}" placeholder="例）GRビル 101号">
               
               @if ($errors->has('receiver.address_2'))
                   <div class="help-block text-danger receiver-error">
                       <span class="fa fa-exclamation form-control-feedback"></span>
                       <span>{{ $errors->first('receiver.address_2') }}</span>
                   </div>
               @endif
           </td>
        </tr>
        
        {{--
        <tr class="form-group">
            <th>配送先住所3（）</th>
              <td>
               <input type="text" class="form-control col-md-12{{ $errors->has('receiver.address_3') ? ' is-invalid' : '' }}" name="receiver[address_3]" value="{{ Ctm::isOld() ? old('receiver.address_3') : (Session::has('all.data.receiver') ? session('all.data.receiver.address_3') : '') }}" placeholder="GRビル 101号">
               
               @if ($errors->has('receiver.address_3'))
                   <div class="help-block text-danger receiver-error">
                       <span class="fa fa-exclamation form-control-feedback"></span>
                       <span>{{ $errors->first('receiver.address_3') }}</span>
                   </div>
               @endif
           </td>
        </tr>
        --}}
        
        </table>
   </div>


