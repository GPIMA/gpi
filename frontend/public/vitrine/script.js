const header=document.querySelector('[data-header]');
const nav=document.querySelector('[data-nav]');
const menu=document.querySelector('[data-menu]');
const progress=document.querySelector('.progress');
const topButton=document.querySelector('[data-top]');
const form=document.querySelector('[data-form]');
const formMessage=document.querySelector('[data-form-message]');
const navLinks=[...document.querySelectorAll('.nav a')];
const sections=[...document.querySelectorAll('main section[id]')];

function updateScrollState(){
  const y=window.scrollY;
  const max=document.documentElement.scrollHeight-window.innerHeight;
  header?.classList.toggle('scrolled',y>24);
  topButton?.classList.toggle('visible',y>520);
  if(progress) progress.style.width=max>0?`${Math.min((y/max)*100,100)}%`:'0%';

  let current=sections[0]?.id;
  for(const section of sections){
    if(section.offsetTop<=y+140) current=section.id;
  }
  navLinks.forEach(link=>link.classList.toggle('active',link.getAttribute('href')===`#${current}`));
}

function toggleMenu(){
  const isOpen=nav?.classList.toggle('open') ?? false;
  menu?.setAttribute('aria-expanded',String(isOpen));
}

function closeMenu(){
  nav?.classList.remove('open');
  menu?.setAttribute('aria-expanded','false');
}

const observer=new IntersectionObserver((entries)=>{
  entries.forEach((entry)=>{
    if(entry.isIntersecting){
      entry.target.classList.add('is-visible');
      observer.unobserve(entry.target);
    }
  });
},{threshold:.12,rootMargin:'0px 0px -40px 0px'});

document.querySelectorAll('.reveal').forEach((element)=>observer.observe(element));
window.addEventListener('scroll',updateScrollState,{passive:true});
window.addEventListener('resize',updateScrollState);
menu?.addEventListener('click',toggleMenu);
navLinks.forEach((link)=>link.addEventListener('click',closeMenu));
topButton?.addEventListener('click',()=>window.scrollTo({top:0,behavior:'smooth'}));
form?.addEventListener('submit',(event)=>{
  event.preventDefault();
  if(formMessage) formMessage.textContent='Message enregistré côté interface. Le formulaire peut être connecté au backend ensuite.';
  form.reset();
});
updateScrollState();
