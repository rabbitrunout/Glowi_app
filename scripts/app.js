document.addEventListener('DOMContentLoaded', function () {
  const overlay = document.getElementById('modalOverlay');

  // ===== Кнопка "Показать все запросы" =====
  const showAllBtn = document.getElementById('showAllRequests');
  if (showAllBtn) {
    showAllBtn.addEventListener('click', function() {
      document.querySelectorAll('.hidden-request').forEach(el => el.style.display = 'block');
      showAllBtn.style.display = 'none';
      lucide.createIcons();
    });
  }

  // ===== Универсальные модалки =====
  function openModal(id) {
    const modal = document.getElementById(id);
    if (modal && overlay) {
      modal.style.display = 'block';
      overlay.style.display = 'block';
    }
  }

  function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal && overlay) {
      modal.style.display = 'none';
      overlay.style.display = 'none';
    }
  }

  function openActiveModal(id) {
    const modal = document.getElementById(id);
    if (modal && overlay) {
      modal.classList.add('active');
      overlay.classList.add('active');
    }
  }

  function closeActiveModal(id) {
    const modal = document.getElementById(id);
    if (modal && overlay) {
      modal.classList.remove('active');
      overlay.classList.remove('active');
    }
  }

  // Overlay закрывает все модалки
  if (overlay) {
    overlay.addEventListener('click', () => {
      document.querySelectorAll('.modal, .glowi-modal').forEach(m => m.style.display = 'none');
      document.querySelectorAll('.modal.active, .glowi-modal.active').forEach(m => m.classList.remove('active'));
      overlay.style.display = 'none';
      overlay.classList.remove('active');
    });
  }

  // Кнопки закрытия
  document.querySelectorAll('.modal .close, .glowi-modal .close').forEach(btn => {
    btn.addEventListener('click', function () {
      const modal = this.closest('.modal, .glowi-modal');
      if (modal) closeModal(modal.id);
    });
  });

  // ===== Calendar =====
  const calendarEl = document.getElementById('calendar');
  const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'en',
    initialView: 'dayGridMonth',
    editable: true,
    selectable: true,
    eventSources: [
      fcEventsFromPHP
    ],
    eventClassNames: (arg) => {
      const t = arg.event.extendedProps?.eventType;
      if (t === 'competition') return ['competition-event'];
      if (t === 'training') return ['training-event'];
      return [];
    },
    eventDidMount: (info) => {
      const emojiMap = { 'schedule':'📅','training':'🏋️','competition':'🏆','achievement':'🥇','private_lesson':'🎯' };
      const prefix = emojiMap[info.event.extendedProps?.eventType] || '';
      const titleEl = info.el.querySelector('.fc-event-title');
      if (titleEl) titleEl.innerHTML = `${prefix} ${info.event.title}`;
    },
    eventClick: function(info) {
      openGlowiModal(info.event.title, info.event.extendedProps?.description || 'Без описания');
    },
    select: function(info) {
      const title = prompt("Название нового события:");
      if (!title) return;
      const type = prompt("Тип события: training/competition (по умолчанию training)") || 'training';
      const newEvent = { title, start: info.startStr, end: info.endStr || '', allDay:false, eventType:type };
      fetch(`add_event.php?childID=${childID}`, {
        method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(newEvent)
      })
      .then(res=>res.json())
      .then(data=>{
        if(data.success) {
          calendar.addEvent({ id:data.id, title, start:info.startStr, end:info.endStr||'', allDay:false, extendedProps:{eventType:type} });
        } else alert('Ошибка добавления: '+(data.error||'неизвестно'));
      });
    },
    eventDrop: updateEvent,
    eventResize: updateEvent
  });

  calendar.render();

  function updateEvent(info) {
    const event = info.event || info;
    fetch('update_event.php', {
      method:'POST', headers:{'Content-Type':'application/json'}, 
      body:JSON.stringify({ id:event.id, title:event.title, start:event.start.toISOString(), end:event.end?event.end.toISOString():null, allDay:event.allDay })
    })
    .then(res=>res.json())
    .then(data=>{ if(!data.success) alert('Ошибка обновления: '+(data.error||'')) });
  }

  // ===== Swiper =====
  new Swiper('.swiper',{
    loop:true,
    slidesPerView:1,
    spaceBetween:20,
    autoplay:{delay:3000},
    breakpoints:{768:{slidesPerView:2},1024:{slidesPerView:3}}
  });

  // ===== Lucide =====
  lucide.createIcons();

  // ===== Экспорт функций =====
  window.openModal = openModal;
  window.closeModal = closeModal;
  window.openActiveModal = openActiveModal;
  window.closeActiveModal = closeActiveModal;
});

// ===== Редактирование достижений =====
function editAchievement(ach) {
  document.getElementById("editID").value = ach.achievementID;
  document.getElementById("editTitle").value = ach.title;
  document.getElementById("editType").value = ach.type;
  document.getElementById("editDate").value = ach.dateAwarded;
  document.getElementById("editPlace").value = ach.place||"";
  document.getElementById("editMedal").value = ach.medal||"none";
  openActiveModal('editModal');
}

function closeEditModal() { closeActiveModal('editModal'); }

// ===== Glowi модалки просмотра (Календарь) =====
function openGlowiModal(title, details) {
  const overlay = document.getElementById("modalOverlay");
  const modal = document.getElementById("viewEventModal");

  document.getElementById("viewEventTitle").innerHTML = `<i data-lucide="calendar-days"></i> ${title}`;
  document.getElementById("viewEventDetails").innerText = details;

  if (modal && overlay) {
    modal.style.display = 'block';
    overlay.style.display = 'block';
  }

  lucide.createIcons();
}

function closeGlowiModal() {
  const overlay = document.getElementById("modalOverlay");
  const modal = document.getElementById("viewEventModal");
  if (modal && overlay) {
    modal.style.display = 'none';
    overlay.style.display = 'none';
  }
}
