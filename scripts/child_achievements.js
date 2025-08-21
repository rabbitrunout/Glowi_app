// js/child_achievements.js

// открывает модалку и подставляет данные в форму
function editAchievement(ach) {
    document.getElementById('editID').value = ach.achievementID;
    document.getElementById('editTitle').value = ach.title;
    document.getElementById('editType').value = ach.type || 'medal';
    document.getElementById('editDate').value = ach.dateAwarded;
    document.getElementById('editPlace').value = ach.place || '';
    document.getElementById('editMedal').value = ach.medal || 'none';

    // показать модалку
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('editModal').style.display = 'block';
}

// закрывает модалку
function closeAchievementModal() {
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('editModal').style.display = 'none';
}

// закрытие по клику на фон
document.getElementById('overlay').addEventListener('click', closeAchievementModal);
