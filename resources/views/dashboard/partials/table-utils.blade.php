<script>
function initDashboardTables(root=document){
  root.querySelectorAll('[data-table-wrapper]').forEach(wrap=>{
    const table=wrap.querySelector('[data-table]'); if(!table) return;
    const tbody=table.querySelector('tbody'); if(!tbody) return;
    const search=wrap.querySelector('[data-table-search]');
    const pager=wrap.querySelector('[data-table-pagination]');
    const headers=[...table.querySelectorAll('th[data-sort]')];
    let rows=[...tbody.querySelectorAll('tr')], page=1, perPage=15, sortKey=null, asc=true, q='';
    function txt(tr,i){return (tr.children[i]?.innerText||'').trim().toLowerCase();}
    function render(){
      let f=rows.filter(r=>r.innerText.toLowerCase().includes(q));
      if(sortKey!==null){f=f.sort((a,b)=>{const av=txt(a,sortKey), bv=txt(b,sortKey); return asc?av.localeCompare(bv,undefined,{numeric:true}):bv.localeCompare(av,undefined,{numeric:true});});}
      const total=Math.max(1,Math.ceil(f.length/perPage)); if(page>total) page=total;
      rows.forEach(r=>r.style.display='none');
      f.slice((page-1)*perPage,page*perPage).forEach(r=>r.style.display='');
      if(pager) pager.textContent=`Page ${page}/${total} • ${f.length} rows`;
    }
    if(search) search.addEventListener('input',e=>{q=e.target.value.toLowerCase(); page=1; render();});
    headers.forEach((h,idx)=>h.addEventListener('click',()=>{if(sortKey===idx) asc=!asc; else {sortKey=idx; asc=true;} render();}));
    render();
  });
}
window.initDashboardTables=initDashboardTables;
</script>
