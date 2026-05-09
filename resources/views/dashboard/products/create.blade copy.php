<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Advanced Product System</title>

<style>
body{font-family:Arial;background:#f3f4f6;margin:0}
.container{max-width:1200px;margin:40px auto;background:#fff;padding:30px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.05)}
select,input,button{padding:8px 12px;border-radius:6px;border:1px solid #ddd;margin:5px 0}
button{background:#111827;color:#fff;border:none;cursor:pointer}
button:hover{opacity:.9}
.hidden{display:none}
.image-preview{width:70px;height:70px;object-fit:cover;border-radius:6px}
.variant-table{width:100%;border-collapse:collapse;margin-top:20px}
.variant-table th,.variant-table td{border:1px solid #ddd;padding:8px;text-align:center}
.preview-box{display:inline-block;margin:5px;text-align:center}
.small-btn{padding:4px 8px;font-size:12px;margin-top:3px}
hr{margin:25px 0}
</style>
</head>
<body>

<div class="container">

<h2>Product Manager</h2>

<label>Product Type</label>
<select id="productType">
  <option value="simple">Simple</option>
  <option value="variable">Variable</option>
</select>

<hr>

<!-- SIMPLE -->
<div id="simpleSection">

<h3>Pricing</h3>
<input type="number" placeholder="Main Price">
<input type="number" placeholder="Compare Price">

<h3>Simple Product Images (Max 5)</h3>
<input type="file" id="simpleImagesInput" multiple accept="image/*">
<div id="simpleImagesContainer"></div>

</div>

<!-- VARIABLE -->
<div id="variableSection" class="hidden">

<h3>Main Pricing</h3>
<input type="number" placeholder="Main Price">
<input type="number" placeholder="Main Compare Price">

<h3>Main Image</h3>
<input type="file" id="mainImageInput" accept="image/*">
<div id="mainImagePreview"></div>

<hr>

<h3>Attributes</h3>

<select id="attributeSelect">
  <option value="">Select Attribute</option>
  <option value="Size">Size</option>
  <option value="Color">Color</option>
  <option value="RAM">RAM</option>
  <option value="Storage">Storage</option>
</select>
<button id="addAttributeBtn">Add Attribute</button>

<div id="attributesContainer"></div>

<button id="generateBtn">Generate Variations</button>

<table class="variant-table hidden" id="variantTable">
<thead><tr id="variantHeader"></tr></thead>
<tbody id="variantBody"></tbody>
</table>

</div>
</div>

<script>

/* ================== DATA ================== */

const attributeLibrary={
  Size:["S","M","L","XL"],
  Color:["Black","White","Red"],
  RAM:["4GB","8GB"],
  Storage:["128GB","256GB"]
};

let state={
  attributes:[],
  variants:new Map(),
  simpleImages:[],
  mainImage:null,
  variantId:0
};

/* ================== TYPE SWITCH ================== */

productType.addEventListener("change",()=>{
  simpleSection.classList.toggle("hidden",productType.value!=="simple");
  variableSection.classList.toggle("hidden",productType.value!=="variable");
});

/* ================= SIMPLE ================= */

simpleImagesInput.addEventListener("change",e=>{
  const files=[...e.target.files];
  if(state.simpleImages.length+files.length>5){
    alert("Max 5 images");
    return;
  }
  files.forEach(f=>{
    state.simpleImages.push({
      file:f,
      url:URL.createObjectURL(f)
    });
  });
  renderSimpleImages();
});

function renderSimpleImages(){
  simpleImagesContainer.innerHTML="";
  state.simpleImages.forEach((img,i)=>{
    simpleImagesContainer.innerHTML+=`
      <div class="preview-box">
        <img src="${img.url}" class="image-preview"><br>
        <button class="small-btn" onclick="replaceSimple(${i})">Edit</button>
        <button class="small-btn" onclick="deleteSimple(${i})">Delete</button>
      </div>
    `;
  });
}

function deleteSimple(i){
  state.simpleImages.splice(i,1);
  renderSimpleImages();
}

function replaceSimple(i){
  const input=document.createElement("input");
  input.type="file";
  input.accept="image/*";
  input.onchange=e=>{
    const file=e.target.files[0];
    state.simpleImages[i]={
      file,
      url:URL.createObjectURL(file)
    };
    renderSimpleImages();
  };
  input.click();
}

/* ================= MAIN IMAGE ================= */

mainImageInput.addEventListener("change",e=>{
  const file=e.target.files[0];
  if(!file)return;
  state.mainImage={
    file,
    url:URL.createObjectURL(file)
  };
  renderMainImage();
});

function renderMainImage(){
  mainImagePreview.innerHTML="";
  if(!state.mainImage)return;

  mainImagePreview.innerHTML=`
    <div class="preview-box">
      <img src="${state.mainImage.url}" class="image-preview"><br>
      <button class="small-btn" onclick="replaceMain()">Edit</button>
      <button class="small-btn" onclick="deleteMain()">Delete</button>
    </div>
  `;
}

function deleteMain(){
  state.mainImage=null;
  renderMainImage();
}

function replaceMain(){
  const input=document.createElement("input");
  input.type="file";
  input.accept="image/*";
  input.onchange=e=>{
    const file=e.target.files[0];
    state.mainImage={
      file,
      url:URL.createObjectURL(file)
    };
    renderMainImage();
  };
  input.click();
}

/* ================= ATTRIBUTES ================= */

addAttributeBtn.addEventListener("click",()=>{
  const name=attributeSelect.value;
  if(!name)return;

  if(state.attributes.find(a=>a.name===name)){
    alert("Already added");
    return;
  }

  state.attributes.push({
    name,
    values:[],
    library:[...attributeLibrary[name]]
  });

  renderAttributes();
});

function renderAttributes(){
  attributesContainer.innerHTML="";

  state.attributes.forEach((attr,i)=>{

    attributesContainer.innerHTML+=`
      <div>
        <strong>${attr.name}</strong><br>

        ${attr.library.map(v=>`
          <label>
            <input type="checkbox"
              ${attr.values.includes(v)?'checked':''}
              onchange="toggleValue(${i},'${v}',this)">
            ${v}
          </label>
        `).join(" ")}

        <br><br>

        <input id="new_value_${i}" placeholder="New value" style="width:150px">
        <button class="small-btn" onclick="addNewValue(${i})">Add</button>

        <hr>
      </div>
    `;
  });
}

function toggleValue(i,val,el){
  if(el.checked)
    state.attributes[i].values.push(val);
  else
    state.attributes[i].values=
      state.attributes[i].values.filter(v=>v!==val);
}

function addNewValue(i){
  const input=document.getElementById("new_value_"+i);
  const val=input.value.trim();

  if(!val){
    alert("Enter value");
    return;
  }

  state.attributes[i].library.push(val);
  input.value="";
  renderAttributes();
}

/* ================= VARIATIONS ================= */

generateBtn.addEventListener("click",()=>{

  if(state.attributes.length===0){
    alert("At least one attribute required");
    return;
  }

  if(state.attributes.some(a=>a.values.length===0)){
    alert("Select values first");
    return;
  }

  const combos=getCombos(state.attributes.map(a=>a.values));

  state.variants.clear();

  combos.forEach(combo=>{
    const id=state.variantId++;
    state.variants.set(id,{
      id,
      combo,
      sku:combo.join("-"),
      price:0,
      compare_price:0,
      stock:10,
      image:null
    });
  });

  renderVariants();
});

function getCombos(arrays){
  return arrays.reduce(
    (a,b)=>a.flatMap(d=>b.map(e=>[...d,e])),
    [[]]
  );
}

function renderVariants(){

  variantHeader.innerHTML="";
  variantBody.innerHTML="";

  state.attributes.forEach(a=>{
    variantHeader.innerHTML+=`<th>${a.name}</th>`;
  });

  variantHeader.innerHTML+=
    `<th>Image</th><th>SKU</th><th>Price</th><th>Compare</th><th>Stock</th><th>Delete</th>`;

  state.variants.forEach(v=>{
    const tr=document.createElement("tr");

    v.combo.forEach(c=>{
      tr.innerHTML+=`<td>${c}</td>`;
    });

    tr.innerHTML+=`
      <td>
        ${v.image?`
          <img src="${v.image.url}" class="image-preview"><br>
          <button onclick="deleteVarImage(${v.id})">Delete</button>
          <button onclick="replaceVarImage(${v.id})">Edit</button>
        `:
        `<input type="file"
          onchange="changeVarImage(${v.id},this)">`
        }
      </td>
      <td><input value="${v.sku}"
        oninput="editField(${v.id},'sku',this.value)"></td>
      <td><input type="number" value="${v.price}"
        oninput="editField(${v.id},'price',this.value)"></td>
      <td><input type="number" value="${v.compare_price}"
        oninput="editField(${v.id},'compare_price',this.value)"></td>
      <td><input type="number" value="${v.stock}"
        oninput="editField(${v.id},'stock',this.value)"></td>
      <td><button onclick="deleteVariant(${v.id})">X</button></td>
    `;

    variantBody.appendChild(tr);
  });

  variantTable.classList.remove("hidden");
}

function editField(id,field,value){
  state.variants.get(id)[field]=value;
}

function changeVarImage(id,input){
  const file=input.files[0];
  state.variants.get(id).image={
    file,
    url:URL.createObjectURL(file)
  };
  renderVariants();
}

function deleteVarImage(id){
  state.variants.get(id).image=null;
  renderVariants();
}

function replaceVarImage(id){
  const input=document.createElement("input");
  input.type="file";
  input.accept="image/*";
  input.onchange=e=>{
    const file=e.target.files[0];
    state.variants.get(id).image={
      file,
      url:URL.createObjectURL(file)
    };
    renderVariants();
  };
  input.click();
}

function deleteVariant(id){
  state.variants.delete(id);
  renderVariants();
}

</script>

</body>
</html>