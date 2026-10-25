# Preloader Studio 2.6.5 — GitHub থেকে WordPress.org-এ প্রকাশ

**প্রয়োজন:** GitHub অ্যাকাউন্ট, WordPress.org `codelith` অ্যাকাউন্টের SVN access, এবং SVN-এর আলাদা পাসওয়ার্ড।

১. `preloader-studio-github-source-v2.6.5.zip` Extract করো। Extract করা ফোল্ডারের **ভেতরের সব ফাইল** GitHub-এর নতুন PUBLIC repository-র মূল স্থানে আপলোড করো। ZIP ফাইলটি একা আপলোড করলে কাজ হবে না।

২. GitHub-এ `preloader-studio.php`, `readme.txt`, `assets/`, `includes/`, `.github/workflows/deploy-wordpress.yml` এবং `.distignore` আছে কি না দেখো। **এই `.github` লুকানো ফোল্ডারটি না থাকলে স্বয়ংক্রিয় প্রকাশ হবে না।**

৩. GitHub repository → **Settings → Secrets and variables → Actions**-এ দুটি Repository secret যোগ করো:

- `SVN_USERNAME` : `codelith`
- `SVN_PASSWORD` : WordPress.org-এর **SVN-specific** password, যা https://profiles.wordpress.org/me/profile/edit/group/3/?screen=svn-password থেকে সেট/সংগ্রহ করা যায়।

**পাসওয়ার্ড চ্যাটে, ইমেইলে বা GitHub কোডের মধ্যে লিখবে না।**

৪. Repository → **Releases → Draft a new release** → Create new tag `2.6.5` from `main` → Publish release। **`v2.6.5` নয়, ঠিক `2.6.5`** দেবে। প্রি-রিলিজ চেক করবে না।

৫. GitHub → **Actions**-এ `Publish Preloader Studio to WordPress.org` workflow সফল হয়েছে কি না দেখো। Permission denied হলে WordPress.org-এ `codelith`-এর SVN commit access যাচাই করো।

৬. প্রকাশের পরে প্লাগইন পেজ: https://wordpress.org/plugins/preloader-studio-animated-loading-screens/

GitHub-এ কোড রাখলেই WordPress.org-এ পাবলিশ হয় না। উপরের Action SVN-এ বাস্তব রিলিজ পাঠায়।

**প্রকাশের আগে:** WordPress staging সাইটে activation, Preloader, Mobile Navigation, WooCommerce Product Layout, Login/Register, Password Reset এবং Security Plugin compatibility পরীক্ষা করে নাও। Plugin Check-এর আগের সবুজ রিপোর্ট এই রিলিজের মূল ফাইলের ওপর ছিল; এখানে শুধু Author ও Contributors `codelith` করা হয়েছে।
