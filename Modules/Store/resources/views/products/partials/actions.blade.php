<x-admin.table.actions :actions="[
['icon'=>'edit','label'=>'Düzenle','url'=>route('admin.store.edit',['product'=>$product,'lang'=>$languageCode]),'permission'=>'store.update'],
['type'=>'form','icon'=>'trash','label'=>'Arşivle','url'=>route('admin.store.destroy',$product),'permission'=>'store.delete','method'=>'DELETE','confirm'=>'Ürün satıştan kaldırılıp arşivlensin mi?']
]" />
