// document.addEventListener('DOMContentLoaded', function() {
//     // Like button click
//     document.querySelectorAll('.like-btn').forEach(function(button) {
//         button.addEventListener('click', function() {
//             const postId = this.getAttribute('data-id');
//             fetch(`/post/${postId}/ajax/like`, {
//                 method: 'POST',
//                 headers: {
//                     'X-Requested-With': 'XMLHttpRequest',
//                     'Content-Type': 'application/json'
//                 },
//                 body: JSON.stringify({})
//             }).then(response => response.json()).then(data => {
//                 if (!data.error) {
//                     document.querySelector('.like-count').innerText = `${data.likes} likes`;
//                     document.querySelector('.dislike-count').innerText = `${data.dislikes} dislikes`;
//                 } else {
//                     alert(data.error);
//                 }
//             });
//         });
//     });
//
//     // Dislike button click
//     document.querySelectorAll('.dislike-btn').forEach(function(button) {
//         button.addEventListener('click', function() {
//             const postId = this.getAttribute('data-id');
//             fetch(`/post/${postId}/ajax/dislike`, {
//                 method: 'POST',
//                 headers: {
//                     'X-Requested-With': 'XMLHttpRequest',
//                     'Content-Type': 'application/json'
//                 },
//                 body: JSON.stringify({})
//             }).then(response => response.json()).then(data => {
//                 if (!data.error) {
//                     document.querySelector('.like-count').innerText = `${data.likes} likes`;
//                     document.querySelector('.dislike-count').innerText = `${data.dislikes} dislikes`;
//                 } else {
//                     alert(data.error);
//                 }
//             });
//         });
//     });
//
//     // Comment like button click
//     document.querySelectorAll('.comment-like-btn').forEach(function(button) {
//         button.addEventListener('click', function() {
//             const commentId = this.getAttribute('data-id');
//             fetch(`/comment/${commentId}/ajax/like`, {
//                 method: 'POST',
//                 headers: {
//                     'X-Requested-With': 'XMLHttpRequest',
//                     'Content-Type': 'application/json'
//                 },
//                 body: JSON.stringify({})
//             }).then(response => response.json()).then(data => {
//                 if (!data.error) {
//                     const likeCountElement = button.nextElementSibling;
//                     const dislikeCountElement = likeCountElement.nextElementSibling.nextElementSibling;
//                     likeCountElement.innerText = `${data.likes} likes`;
//                     dislikeCountElement.innerText = `${data.dislikes} dislikes`;
//                 } else {
//                     alert(data.error);
//                 }
//             });
//         });
//     });
//
//     // Comment dislike button click
//     document.querySelectorAll('.comment-dislike-btn').forEach(function(button) {
//         button.addEventListener('click', function() {
//             const commentId = this.getAttribute('data-id');
//             fetch(`/comment/${commentId}/ajax/dislike`, {
//                 method: 'POST',
//                 headers: {
//                     'X-Requested-With': 'XMLHttpRequest',
//                     'Content-Type': 'application/json'
//                 },
//                 body: JSON.stringify({})
//             }).then(response => response.json()).then(data => {
//                 if (!data.error) {
//                     const likeCountElement = button.previousElementSibling.previousElementSibling;
//                     const dislikeCountElement = button.nextElementSibling;
//                     likeCountElement.innerText = `${data.likes} likes`;
//                     dislikeCountElement.innerText = `${data.dislikes} dislikes`;
//                 } else {
//                     alert(data.error);
//                 }
//             });
//         });
//     });
//
//     // Comment form submission
//     document.getElementById('comment-form').addEventListener('submit', function(event) {
//         event.preventDefault();
//         const formData = new FormData(this);
//         fetch(this.action, {
//             method: 'POST',
//             body: formData,
//             headers: {
//                 'X-Requested-With': 'XMLHttpRequest'
//             }
//         }).then(response => response.json()).then(data => {
//             if (data.success) {
//                 const commentList = document.getElementById('comment-list');
//                 const newComment = document.createElement('li');
//                 newComment.innerHTML = `
//                     ${data.comment.content} - par ${data.comment.author} le ${data.comment.createdAt}
//                     <div>
//                         <button class="comment-like-btn" data-id="${data.comment.id}" ${!isGranted('ROLE_USER_REGISTERED') ? 'disabled title="Vous devez valider votre e-mail pour pouvoir interagir"' : ''}>Like</button>
//                         <span class="comment-like-count">0 likes</span>
//                         <button class="comment-dislike-btn" data-id="${data.comment.id}" ${!isGranted('ROLE_USER_REGISTERED') ? 'disabled title="Vous devez valider votre e-mail pour pouvoir interagir"' : ''}>Dislike</button>
//                         <span class="comment-dislike-count">0 dislikes</span>
//                     </div>
//                 `;
//                 commentList.appendChild(newComment);
//                 this.reset();
//             } else {
//                 alert(data.error);
//             }
//         });
//     });
// });