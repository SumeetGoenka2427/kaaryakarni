/* ==========================================================================
   Khandelwal Samaj Membership Portal — DEMO data store
   --------------------------------------------------------------------------
   This is a localStorage-backed mock database used ONLY because there is
   no backend/API yet. It exists so the "web" (public + member) app and the
   "admin" app can be built and demonstrated against the SAME data, and so
   admin actions (approve a business, verify a payment, publish a banner...)
   are immediately visible on the public site.

   IMPORTANT — this must be replaced by real server-side persistence
   (CLAUDE.md sections 19/32/33). Nothing here performs real authentication,
   real payment capture, or real authorization. Do not ship this file.

   Because this runs from local files / static hosting without a server,
   localStorage is scoped per-origin. Serve the whole `kaaryakarni` folder
   from ONE local static server (see README.md) so that `web/` and `admin/`
   share an origin and therefore share this data. Opening the .html files
   directly by double-click (file://) will NOT share data between folders.
   ========================================================================== */
(function (global) {
  'use strict';

  var DB_KEY = 'kk_demo_db_v1';

  function uid(prefix) {
    return (prefix || 'id') + '_' + Math.random().toString(36).slice(2, 10) + Date.now().toString(36).slice(-4);
  }

  function nowIso() { return new Date().toISOString(); }

  function seed() {
    return {
      settings: {
        orgName: 'Khandelwal Samaj, Jaipur',
        membershipFee: 5000,
        donorNamesPublic: true
      },
      adminUsers: [
        { id: 'admin_1', username: 'admin', password: 'admin123', name: 'Samaj Admin', role: 'super-admin' }
      ],
      areas: [
        { id: uid('area'), name: 'Chandpole', active: true, order: 1 },
        { id: uid('area'), name: 'Johari Bazaar', active: true, order: 2 },
        { id: uid('area'), name: 'Malviya Nagar', active: true, order: 3 },
        { id: uid('area'), name: 'Vaishali Nagar', active: true, order: 4 },
        { id: uid('area'), name: 'C-Scheme', active: true, order: 5 },
        { id: uid('area'), name: 'Mansarovar', active: true, order: 6 },
        { id: uid('area'), name: 'Raja Park', active: true, order: 7 },
        { id: uid('area'), name: 'Bani Park', active: true, order: 8 },
        { id: uid('area'), name: 'Tonk Road', active: true, order: 9 },
        { id: uid('area'), name: 'Jagatpura', active: true, order: 10 },
        { id: uid('area'), name: 'Vidhyadhar Nagar', active: true, order: 11 },
        { id: uid('area'), name: 'Sanganer', active: true, order: 12 },
        { id: uid('area'), name: 'Jhotwara', active: true, order: 13 },
        { id: uid('area'), name: 'Shastri Nagar', active: true, order: 14 },
        { id: uid('area'), name: 'Civil Lines', active: true, order: 15 },
        { id: uid('area'), name: 'Adarsh Nagar', active: true, order: 16 },
        { id: uid('area'), name: 'Amer Road', active: true, order: 17 },
        { id: uid('area'), name: 'Durgapura', active: true, order: 18 },
        { id: uid('area'), name: 'Pratap Nagar', active: true, order: 19 },
        { id: uid('area'), name: 'Sodala', active: true, order: 20 }
      ],
      members: [
        {
          id: 'mem_seed_1', membershipId: 'KSJ-2025-0001', name: 'Ramesh Khandelwal', mobile: '9876543210',
          email: 'ramesh.k@example.com', dob: '1978-04-12', gender: 'Male', area: 'Chandpole',
          address: 'Chandpole, Jaipur', photo: null, status: 'Lifetime Member',
          verified: true, createdAt: nowIso()
        },
        {
          id: 'mem_seed_2', membershipId: 'KSJ-2025-0002', name: 'Sunita Agarwal', mobile: '9876543211',
          email: '', dob: '1985-09-02', gender: 'Female', area: 'Malviya Nagar',
          address: 'Malviya Nagar, Jaipur', photo: null, status: 'Under Verification',
          verified: false, createdAt: nowIso()
        },
        {
          id: 'mem_seed_3', membershipId: 'KSJ-2025-0003', name: 'Vikas Mittal', mobile: '9876543212',
          email: 'vikas.m@example.com', dob: '1990-01-20', gender: 'Male', area: 'Vaishali Nagar',
          address: 'Vaishali Nagar, Jaipur', photo: null, status: 'Payment Submitted',
          verified: false, createdAt: nowIso()
        }
      ],
      committeeMembers: (function () {
        var leadership = [
          { name: 'Shri R. Khandelwal', designation: 'President', tier: 'leadership', order: 1 },
          { name: 'Smt. S. Agarwal', designation: 'General Secretary', tier: 'leadership', order: 2 },
          { name: 'Shri M. Goyal', designation: 'Treasurer', tier: 'leadership', order: 3 },
          { name: 'Shri V. Mittal', designation: 'Joint Secretary', tier: 'leadership', order: 4 }
        ];
        var generalNames = [
          'Ashok Bansal', 'Kamla Jindal', 'Suresh Singhal', 'Meena Garg', 'Rajesh Tayal', 'Anita Kandoi',
          'Pramod Khandelwal', 'Sarita Agarwal', 'Deepak Mittal', 'Rekha Goyal', 'Naveen Bansal', 'Poonam Singhal',
          'Sanjay Jindal', 'Shobha Garg', 'Vinod Tayal', 'Manisha Kandoi', 'Arun Khandelwal', 'Usha Agarwal',
          'Ramesh Mittal', 'Geeta Goyal', 'Mahesh Bansal', 'Kavita Singhal', 'Dinesh Jindal', 'Nirmala Garg',
          'Pankaj Tayal', 'Seema Kandoi', 'Rakesh Khandelwal', 'Vandana Agarwal', 'Sunil Mittal', 'Alka Goyal',
          'Manoj Bansal', 'Pushpa Singhal', 'Ajay Jindal', 'Ritu Garg', 'Vijay Tayal', 'Sushma Kandoi',
          'Yogesh Khandelwal', 'Neelam Agarwal', 'Girish Mittal', 'Madhu Goyal', 'Rohit Bansal', 'Priya Singhal'
        ];
        var designations = [
          'Executive Committee Member', 'Managing Committee Member', "Women's Wing Coordinator", 'Youth Wing Coordinator',
          'Welfare Committee Member', 'Event Committee Member', 'Advisory Committee Member', 'Membership Committee Member'
        ];
        var general = generalNames.map(function (name, i) {
          return { name: name, designation: designations[i % designations.length], tier: 'general', order: 5 + i };
        });
        return leadership.concat(general).map(function (m, i) {
          return Object.assign({ id: 'comm_' + (i + 1), photo: null }, m);
        });
      })(),
      businesses: [
        { id: 'biz_1', memberId: 'mem_seed_1', name: 'Khandelwal Textiles', category: 'Textiles', area: 'Chandpole',
          description: 'Wholesale & retail fabrics in Chandpole market for over 20 years.', address: 'Shop 14, Textile Market, Chandpole Bazaar, Jaipur',
          phone: '9876543210', website: '', logo: null, status: 'approved', featured: true, createdAt: nowIso() },
        { id: 'biz_2', memberId: 'mem_seed_2', name: 'Dr. Anjali Khandelwal — Dental Clinic', category: 'Healthcare', area: 'Malviya Nagar',
          description: 'General & cosmetic dental clinic.', address: 'Malviya Nagar, Jaipur',
          phone: '9876543211', website: '', logo: null, status: 'approved', featured: false, createdAt: nowIso() },
        { id: 'biz_3', memberId: 'mem_seed_3', name: 'Vikas Interiors', category: 'Real Estate', area: 'Vaishali Nagar',
          description: 'Interior design and property consultancy.', address: 'Vaishali Nagar, Jaipur',
          phone: '9876543212', website: '', logo: null, status: 'pending', featured: false, createdAt: nowIso() },
        { id: 'biz_4', memberId: 'mem_seed_1', name: 'Khandelwal Jewellers', category: 'Jewellery', area: 'Johari Bazaar',
          description: 'Traditional gold and kundan jewellery, trusted by Samaj families for three generations.', address: 'Shop 22, Johari Bazaar, Jaipur',
          phone: '9876543217', website: '', logo: null, status: 'approved', featured: true, createdAt: nowIso() },
        { id: 'biz_5', memberId: 'mem_seed_2', name: 'Jaipur Sweets & Namkeen Bhandar', category: 'Food & Catering', area: 'Chandpole',
          description: 'Traditional Rajasthani sweets and namkeen, and festival/event catering for Samaj functions.', address: 'Near Chandpole Gate, Jaipur',
          phone: '9876543218', website: '', logo: null, status: 'approved', featured: false, createdAt: nowIso() },
        { id: 'biz_6', memberId: 'mem_seed_3', name: 'Bright Future Coaching Institute', category: 'Education', area: 'Mansarovar',
          description: 'Coaching for school, JEE and NEET aspirants, with a special batch for Samaj students.', address: 'Plot 8, Mansarovar, Jaipur',
          phone: '9876543219', website: '', logo: null, status: 'approved', featured: false, createdAt: nowIso() },
        { id: 'biz_7', memberId: 'mem_seed_1', name: 'Agarwal Real Estate Consultants', category: 'Real Estate', area: 'Vaishali Nagar',
          description: 'Residential and commercial property buying, selling and rental consultancy across Jaipur.', address: 'Office 5, Vaishali Nagar Main Road, Jaipur',
          phone: '9876543220', website: '', logo: null, status: 'approved', featured: false, createdAt: nowIso() },
        { id: 'biz_8', memberId: 'mem_seed_2', name: 'Mittal Computers & Electronics', category: 'Electronics', area: 'Tonk Road',
          description: 'Laptops, computer accessories, sales and repair services for home and office.', address: 'Shop 3, Tonk Road, Jaipur',
          phone: '9876543221', website: '', logo: null, status: 'approved', featured: false, createdAt: nowIso() }
      ],
      matrimonial: (function () {
        var IMG = '/web/assets/images/users/';
        var rows = [
          { id: 'mat_1', memberId: 'mem_seed_2', relation: 'Self', name: 'Sunita A.', fatherName: 'Late Shri G. Agarwal', motherName: '',
            age: 27, gender: 'Female', height: "5'3\"", nativePlace: 'Jaipur', area: 'Malviya Nagar', photo: IMG + 'woman-1.jpg',
            education: 'M.Com', occupation: 'Bank Officer', about: 'Profile pending admin approval.', status: 'pending',
            privacy: { showContact: false } },
          { id: 'mat_2', memberId: 'mem_seed_1', relation: 'Son', name: 'Rohit Khandelwal', fatherName: 'Ramesh Khandelwal', motherName: 'Late Smt. K. Khandelwal',
            age: 30, gender: 'Male', height: "5'9\"", nativePlace: 'Jaipur', area: 'Chandpole', photo: IMG + 'man-1.jpg',
            education: 'B.Tech', occupation: 'Business Owner', about: 'Runs the family textile business in Chandpole. Enjoys cricket and weekend family gatherings.', status: 'approved',
            privacy: { showContact: true } },
          { id: 'mat_3', memberId: 'mem_sample_3', relation: 'Self', name: 'Priya Khandelwal', fatherName: 'Suresh Khandelwal', motherName: 'Meena Khandelwal',
            age: 26, gender: 'Female', height: "5'4\"", nativePlace: 'Jaipur', area: 'Vaishali Nagar', photo: IMG + 'woman-2.jpg',
            education: 'B.Com', occupation: 'Boutique Owner', about: 'Runs a small fashion boutique in Vaishali Nagar. Loves painting and classical dance.', status: 'approved',
            privacy: { showContact: false } },
          { id: 'mat_4', memberId: 'mem_sample_4', relation: 'Self', name: 'Rohan Agarwal', fatherName: 'Vinod Agarwal', motherName: 'Sarita Agarwal',
            age: 29, gender: 'Male', height: "5'10\"", nativePlace: 'Jaipur', area: 'C-Scheme', photo: IMG + 'man-2.jpg',
            education: 'MBA', occupation: 'Marketing Manager', about: 'Works with a retail brand in Jaipur. Enjoys travelling and photography.', status: 'approved',
            privacy: { showContact: true } },
          { id: 'mat_5', memberId: 'mem_sample_5', relation: 'Self', name: 'Neha Mittal', fatherName: 'Anil Mittal', motherName: 'Rekha Mittal',
            age: 24, gender: 'Female', height: "5'2\"", nativePlace: 'Jaipur', area: 'Malviya Nagar', photo: IMG + 'woman-1.jpg',
            education: 'MBBS', occupation: 'Doctor', about: 'Junior resident doctor at a Jaipur hospital. Family-oriented, enjoys reading.', status: 'approved',
            privacy: { showContact: false } },
          { id: 'mat_6', memberId: 'mem_sample_6', relation: 'Self', name: 'Aditya Goyal', fatherName: 'Prakash Goyal', motherName: 'Usha Goyal',
            age: 31, gender: 'Male', height: "5'8\"", nativePlace: 'Jaipur', area: 'Bani Park', photo: IMG + 'man-3.jpg',
            education: 'Chartered Accountant', occupation: 'CA — Own Practice', about: 'Runs an independent CA practice in Bani Park. Enjoys badminton on weekends.', status: 'approved',
            privacy: { showContact: true } },
          { id: 'mat_7', memberId: 'mem_sample_7', relation: 'Daughter', name: 'Kavya Bansal', fatherName: 'Mahesh Bansal', motherName: 'Sunita Bansal',
            age: 28, gender: 'Female', height: "5'5\"", nativePlace: 'Jaipur', area: 'Mansarovar', photo: IMG + 'woman-2.jpg',
            education: 'M.A. Fashion Design', occupation: 'Boutique Designer', about: 'Registered by her family. Designs bridal wear for a Jaipur label.', status: 'approved',
            privacy: { showContact: false } },
          { id: 'mat_8', memberId: 'mem_sample_8', relation: 'Self', name: 'Siddharth Jindal', fatherName: 'Ramesh Jindal', motherName: 'Kamla Jindal',
            age: 27, gender: 'Male', height: "5'9\"", nativePlace: 'Jaipur', area: 'Raja Park', photo: IMG + 'man-4.jpg',
            education: 'B.Tech', occupation: 'Software Engineer', about: 'Works at an IT services firm in Jaipur. Enjoys trekking and cooking.', status: 'approved',
            privacy: { showContact: true } },
          { id: 'mat_9', memberId: 'mem_sample_9', relation: 'Self', name: 'Ananya Singhal', fatherName: 'Dinesh Singhal', motherName: 'Poonam Singhal',
            age: 25, gender: 'Female', height: "5'3\"", nativePlace: 'Jaipur', area: 'Civil Lines', photo: IMG + 'woman-1.jpg',
            education: 'B.Sc Nursing', occupation: 'Staff Nurse', about: 'Works at a private hospital in Jaipur. Enjoys music and volunteering.', status: 'approved',
            privacy: { showContact: false } },
          { id: 'mat_10', memberId: 'mem_sample_10', relation: 'Self', name: 'Varun Kandoi', fatherName: 'Ashok Kandoi', motherName: 'Anita Kandoi',
            age: 30, gender: 'Male', height: "5'7\"", nativePlace: 'Jaipur', area: 'Sanganer', photo: IMG + 'man-1.jpg',
            education: 'MBA Finance', occupation: 'Bank Manager', about: 'Branch manager at a nationalised bank. Enjoys reading and chess.', status: 'approved',
            privacy: { showContact: true } },
          { id: 'mat_11', memberId: 'mem_sample_11', relation: 'Self', name: 'Ritika Tayal', fatherName: 'Vijay Tayal', motherName: 'Sushma Tayal',
            age: 27, gender: 'Female', height: "5'4\"", nativePlace: 'Jaipur', area: 'Jagatpura', photo: IMG + 'woman-2.jpg',
            education: 'B.Ed', occupation: 'School Teacher', about: 'Teaches at a school in Jagatpura. Enjoys gardening and classical music.', status: 'approved',
            privacy: { showContact: false } }
        ];
        return rows.map(function (r) {
          var photo = r.photo; delete r.photo;
          return Object.assign({ photos: [photo], bioDataPdf: null, createdAt: nowIso() }, r);
        });
      })(),
      campaigns: [
        { id: 'camp_samaj', slug: 'samaj-donation', name: 'Samaj Donation', description: 'Our permanent, always-open donation fund supporting general Samaj welfare and operations.',
          target: 750000, raised: 482600, donorCount: 312, permanent: true, active: true, order: 1, startDate: null, endDate: null,
          hasTickets: false, ticketLabel: '', ticketPrice: 0 },
        { id: 'camp_diwali', slug: 'diwali-utsav', name: 'Diwali Utsav Donation', description: "Contribute towards this year's community Diwali celebrations, decoration and cultural programs, or purchase an entry pass for the Utsav evening.",
          target: 400000, raised: 186400, donorCount: 128, permanent: false, active: true, order: 2, startDate: null, endDate: null,
          hasTickets: true, ticketLabel: 'Diwali Utsav Entry Pass', ticketPrice: 501 },
        { id: 'camp_edu', slug: 'education-support', name: 'Education Support Fund', description: 'Helping meritorious Samaj students with school and college education expenses.',
          target: 300000, raised: 84000, donorCount: 41, permanent: false, active: true, order: 3, startDate: null, endDate: null,
          hasTickets: false, ticketLabel: '', ticketPrice: 0 },
        { id: 'camp_med', slug: 'medical-assistance', name: 'Medical Assistance Fund', description: 'Supporting Samaj families facing medical emergencies with financial assistance.',
          target: 200000, raised: 36200, donorCount: 19, permanent: false, active: true, order: 4, startDate: null, endDate: null,
          hasTickets: false, ticketLabel: '', ticketPrice: 0 }
      ],
      donations: [
        { id: uid('don'), campaignId: 'camp_samaj', donorName: 'Ramesh Khandelwal', donorMobile: '9876543210', amount: 5100, mode: 'online', status: 'successful', refNumber: 'PAYDEMO1001', createdAt: new Date(Date.now() - 2 * 60 * 1000).toISOString() },
        { id: uid('don'), campaignId: 'camp_diwali', donorName: 'Sunita Agarwal', donorMobile: '9876543211', amount: 2100, mode: 'offline', status: 'submitted', refNumber: 'UTR22910', createdAt: new Date(Date.now() - 18 * 60 * 1000).toISOString() },
        { id: uid('don'), campaignId: 'camp_samaj', donorName: 'Vikas Mittal', donorMobile: '9876543212', amount: 1100, mode: 'online', status: 'successful', refNumber: 'PAYDEMO1002', createdAt: new Date(Date.now() - 47 * 60 * 1000).toISOString() },
        { id: uid('don'), campaignId: 'camp_edu', donorName: 'Anjali Khandelwal', donorMobile: '9876543213', amount: 2500, mode: 'online', status: 'successful', refNumber: 'PAYDEMO1003', createdAt: new Date(Date.now() - 3 * 60 * 60 * 1000).toISOString() },
        { id: uid('don'), campaignId: 'camp_samaj', donorName: 'Deepak Sharma', donorMobile: '9876543214', amount: 501, mode: 'online', status: 'successful', refNumber: 'PAYDEMO1004', createdAt: new Date(Date.now() - 5 * 60 * 60 * 1000).toISOString() },
        { id: uid('don'), campaignId: 'camp_med', donorName: 'Pooja Agarwal', donorMobile: '9876543215', amount: 3100, mode: 'offline', status: 'verified', refNumber: 'UTR22988', createdAt: new Date(Date.now() - 9 * 60 * 60 * 1000).toISOString() },
        { id: uid('don'), campaignId: 'camp_diwali', donorName: 'Rohit Khandelwal', donorMobile: '9876543216', amount: 1500, mode: 'online', status: 'successful', refNumber: 'PAYDEMO1005', createdAt: new Date(Date.now() - 26 * 60 * 60 * 1000).toISOString() }
      ],
      payments: [
        { id: uid('pay'), memberId: 'mem_seed_1', purpose: 'membership', amount: 5000, mode: 'online', provider: 'demo-gateway', providerRef: 'PAYDEMO1000', status: 'successful', createdAt: nowIso() },
        { id: uid('pay'), memberId: 'mem_seed_3', purpose: 'membership', amount: 5000, mode: 'offline', provider: 'offline', providerRef: 'UTR44821', status: 'submitted', createdAt: nowIso() }
      ],
      banners: [
        { id: uid('ban'), title: 'Become a Lifetime Member of Khandelwal Samaj', subtitle: 'One-time registration. No renewals, no recurring fees — membership stays valid for life.',
          ctaLabel: 'Explore Membership', ctaUrl: '../web/membership.html', tone: 'navy', icon: 'community', image: null, order: 1, active: true, startDate: null, endDate: null },
        { id: uid('ban'), title: "Support This Year's Diwali Utsav Celebrations", subtitle: 'Contribute towards community Diwali festivities, lighting and cultural events for Samaj families.',
          ctaLabel: 'Contribute Now', ctaUrl: '../web/donations.html', tone: 'gold', icon: 'diya', image: null, order: 2, active: true, startDate: null, endDate: null },
        { id: uid('ban'), title: 'Discover Trusted Businesses Within the Samaj', subtitle: "Browse admin-approved businesses and professionals from the Khandelwal community in Jaipur.",
          ctaLabel: 'Browse Directory', ctaUrl: '../web/business-directory.html', tone: 'navy', icon: 'business', image: null, order: 3, active: true, startDate: null, endDate: null }
      ],
      bannerConfig: { campaignPanelId: 'camp_diwali' },
      gallery: [
        { id: 'alb_1', title: 'Samaj Annual Function 2025', description: 'Annual Function 2025.', active: true, order: 1,
          images: [{ id: uid('img'), title: 'Photo 1' }, { id: uid('img'), title: 'Photo 2' }, { id: uid('img'), title: 'Photo 3' }, { id: uid('img'), title: 'Photo 4' }] },
        { id: 'alb_2', title: 'Diwali Utsav Celebration', description: 'Diwali Utsav Celebration.', active: true, order: 2,
          images: [{ id: uid('img'), title: 'Photo 1' }, { id: uid('img'), title: 'Photo 2' }, { id: uid('img'), title: 'Photo 3' }, { id: uid('img'), title: 'Photo 4' }] },
        { id: 'alb_3', title: 'Educational Scholarship Ceremony', description: 'Scholarship Ceremony.', active: true, order: 3,
          images: [{ id: uid('img'), title: 'Photo 1' }, { id: uid('img'), title: 'Photo 2' }, { id: uid('img'), title: 'Photo 3' }, { id: uid('img'), title: 'Photo 4' }] }
      ],
      notifications: [
        { id: uid('ntf'), memberId: null, title: 'Samaj Annual General Meeting', message: 'All lifetime members are invited to the AGM at Samaj Bhawan.', type: 'announcement', read: false, createdAt: nowIso() },
        { id: uid('ntf'), memberId: 'mem_seed_1', title: 'Membership Approved', message: 'Your Lifetime Membership has been verified and approved.', type: 'membership', read: false, createdAt: nowIso() },
        { id: uid('ntf'), memberId: 'mem_seed_3', title: 'Payment Submitted', message: 'Your offline membership payment was received and is awaiting admin verification.', type: 'payment', read: false, createdAt: nowIso() }
      ],
      auditLogs: [
        { id: uid('log'), actor: 'admin', action: 'seed', target: 'database', details: 'Demo data seeded.', createdAt: nowIso() }
      ]
    };
  }

  /* Reconciles one seed collection (matched by row id) into an existing
     database: any field a demo seed row has that the saved row is missing
     gets filled in (e.g. an old "camp_diwali" row gains hasTickets/ticketPrice
     without touching its real raised/donorCount). When addMissingItems is
     true, a whole new seed row (new id, e.g. a newly added sample business)
     is appended too — this is what lets "add more sample businesses" reach a
     browser that already saved a database, without a manual data reset.
     Trade-off: if an admin deliberately deleted a seed row by id, this will
     re-add it on next load — acceptable for pre-production demo content. */
  function reconcileCollection(db, defaultItems, key, addMissingItems) {
    if (!Array.isArray(db[key])) { db[key] = []; }
    var existingById = {};
    db[key].forEach(function (item) { if (item && item.id) existingById[item.id] = item; });
    var changed = false;
    (defaultItems || []).forEach(function (def) {
      var existing = existingById[def.id];
      if (!existing) {
        if (addMissingItems) { db[key].push(def); changed = true; }
        return;
      }
      Object.keys(def).forEach(function (k) {
        if (typeof existing[k] === 'undefined') { existing[k] = def[k]; changed = true; }
      });
    });
    return changed;
  }

  /* Fills in any top-level collection/field introduced after a browser already
     saved a demo database (e.g. an older visit before "committeeMembers" or
     other fields existed), then reconciles specific catalog-style collections
     row-by-row so later demo-data additions/field additions reach browsers
     that already have a saved database. Never touches a collection/field the
     db already has a value for — only adds what's missing. */
  function migrate(db) {
    var defaults = seed();
    var changed = false;
    Object.keys(defaults).forEach(function (key) {
      if (typeof db[key] === 'undefined') {
        db[key] = defaults[key];
        changed = true;
      }
    });
    ['campaigns', 'businesses', 'gallery', 'matrimonial'].forEach(function (key) {
      if (reconcileCollection(db, defaults[key], key, true)) changed = true;
    });
    /* One-time correction: earlier demo builds seeded matrimonial photos from
       an avatar-generator service; replace those with the real sample photos
       now bundled in the project, matched by row id. */
    var defaultMatById = {};
    (defaults.matrimonial || []).forEach(function (m) { defaultMatById[m.id] = m; });
    (db.matrimonial || []).forEach(function (m) {
      var def = defaultMatById[m.id];
      if (def && m.photos && m.photos[0] && /dicebear\.com/.test(m.photos[0])) {
        m.photos = def.photos;
        changed = true;
      }
    });
    return changed;
  }

  function load() {
    try {
      var raw = global.localStorage.getItem(DB_KEY);
      if (!raw) {
        var fresh = seed();
        global.localStorage.setItem(DB_KEY, JSON.stringify(fresh));
        return fresh;
      }
      var db = JSON.parse(raw);
      if (migrate(db)) persist(db);
      return db;
    } catch (e) {
      var fallback = seed();
      try { global.localStorage.setItem(DB_KEY, JSON.stringify(fallback)); } catch (e2) {}
      return fallback;
    }
  }

  function persist(db) {
    try { global.localStorage.setItem(DB_KEY, JSON.stringify(db)); } catch (e) {}
  }

  var KKStore = {
    _db: null,

    db: function () {
      if (!this._db) this._db = load();
      return this._db;
    },

    save: function () { persist(this._db); },

    resetDemoData: function () {
      this._db = seed();
      persist(this._db);
      return this._db;
    },

    all: function (collection) {
      var db = this.db();
      return db[collection] || [];
    },

    findById: function (collection, id) {
      var list = this.all(collection);
      for (var i = 0; i < list.length; i++) if (list[i].id === id) return list[i];
      return null;
    },

    add: function (collection, item) {
      var db = this.db();
      if (!db[collection]) db[collection] = [];
      item.id = item.id || uid(collection.slice(0, 3));
      item.createdAt = item.createdAt || nowIso();
      db[collection].push(item);
      this.save();
      return item;
    },

    update: function (collection, id, patch) {
      var db = this.db();
      var list = db[collection] || [];
      for (var i = 0; i < list.length; i++) {
        if (list[i].id === id) {
          list[i] = Object.assign({}, list[i], patch, { updatedAt: nowIso() });
          this.save();
          return list[i];
        }
      }
      return null;
    },

    remove: function (collection, id) {
      var db = this.db();
      db[collection] = (db[collection] || []).filter(function (item) { return item.id !== id; });
      this.save();
    },

    getSettings: function () { return this.db().settings; },
    updateSettings: function (patch) {
      var db = this.db();
      db.settings = Object.assign({}, db.settings, patch);
      this.save();
      return db.settings;
    },

    /* ---------- Domain helpers ---------- */
    getMemberByMobile: function (mobile) {
      var list = this.all('members');
      for (var i = 0; i < list.length; i++) if (list[i].mobile === mobile) return list[i];
      return null;
    },

    generateMembershipId: function () {
      var count = this.all('members').length + 1;
      return 'KSJ-' + new Date().getFullYear() + '-' + String(count).padStart(4, '0');
    },

    getActiveCampaigns: function () {
      return this.all('campaigns').filter(function (c) { return c.active; })
        .sort(function (a, b) { return a.order - b.order; });
    },

    getCampaignBySlug: function (slug) {
      var list = this.all('campaigns');
      for (var i = 0; i < list.length; i++) if (list[i].slug === slug) return list[i];
      return null;
    },

    getApprovedBusinesses: function () {
      return this.all('businesses').filter(function (b) { return b.status === 'approved'; });
    },

    getApprovedMatrimonial: function () {
      return this.all('matrimonial').filter(function (m) { return m.status === 'approved'; });
    },

    getCommitteeMembers: function () {
      return this.all('committeeMembers').slice().sort(function (a, b) { return a.order - b.order; });
    },

    getActiveBanners: function () {
      return this.all('banners').filter(function (b) { return b.active; })
        .sort(function (a, b) { return a.order - b.order; });
    },

    getActiveGalleryAlbums: function () {
      return this.all('gallery').filter(function (a) { return a.active; })
        .sort(function (a, b) { return a.order - b.order; });
    },

    getNotificationsFor: function (memberId) {
      return this.all('notifications').filter(function (n) { return n.memberId === memberId || n.memberId === null; })
        .sort(function (a, b) { return new Date(b.createdAt) - new Date(a.createdAt); });
    },

    addAuditLog: function (actor, action, target, details) {
      return this.add('auditLogs', { actor: actor, action: action, target: target, details: details || '' });
    },

    computeDashboardStats: function () {
      var members = this.all('members');
      var businesses = this.all('businesses');
      var matrimonial = this.all('matrimonial');
      var donations = this.all('donations');
      var campaigns = this.all('campaigns');
      var totalCollected = campaigns.reduce(function (sum, c) { return sum + (c.raised || 0); }, 0);
      return {
        totalMembers: members.length,
        pendingMembers: members.filter(function (m) { return m.status === 'Under Verification' || m.status === 'Pending'; }).length,
        lifetimeMembers: members.filter(function (m) { return m.status === 'Lifetime Member'; }).length,
        pendingBusinesses: businesses.filter(function (b) { return b.status === 'pending'; }).length,
        approvedBusinesses: businesses.filter(function (b) { return b.status === 'approved'; }).length,
        pendingMatrimonial: matrimonial.filter(function (m) { return m.status === 'pending'; }).length,
        totalDonations: totalCollected,
        pendingOfflineDonations: donations.filter(function (d) { return d.mode === 'offline' && d.status === 'submitted'; }).length
      };
    }
  };

  global.KKStore = KKStore;
})(window);
