# Multi-Vendor Booking Platform

## Why I'm building this

If you've ever tried to book a banquet hall, a photography studio, or any small event space, you know how it actually works. You call the owner, wait for a reply, maybe WhatsApp them a date, and just hope nobody else grabbed that slot before you. Payment is half cash, half online, tracked in a notebook or an Excel sheet. If you want a refund because your event got cancelled, that's a phone call and a negotiation, not a process.

This is the same problem Airbnb, BookMyShow, and Urban Company solved at different scales. I'm building a smaller version of it. A platform where vendors list a bookable thing (a venue, a service, whatever), customers can see real availability and book it instantly, and the platform itself handles the money and the disputes so neither side has to just trust the other blindly.

## Who uses it

**Customer** - searches for a venue/service, sees availability that's actually accurate, books a slot, pays for it, can see their booking history, and gets refunded correctly if they cancel within the rules.

**Vendor** - lists their venue/service, sets available slots (like "Saturday 6-10pm"), sees incoming bookings, tracks what they've earned. Needs to never get double-booked.

**Admin** - split into two types, because not everyone who needs to see the data should be able to act on it.

- **Super Admin** - approves new vendors, resolves disputes (customer says they paid and vendor says they didn't, vendor cancelled last minute, etc), can issue manual refunds, and manages other admin accounts.
- **View-Only Admin** - can see everything, every vendor, every booking, every transaction, but can't approve, refund, or edit any of it. This is for someone who needs visibility (support staff, an auditor) without the power to touch money or vendor status.

## The hard parts worth solving

**Double-booking.** Two customers hit "book" on the same slot in the same second. Only one can win. This is a race condition, and the fix is at the database level (locking or unique constraints).

**Booking state machine.** A booking isn't just booked-or-not. It moves through `pending` to `confirmed` to `completed` to `cancelled` (and `refunded`/`disputed` on top of that), and not every transition is legal from every state. Getting this wrong means bugs where a cancelled booking somehow gets reviewed, or a pending booking gets refunded before it was ever paid for.

**Payment with hold, capture, and refund.** I'm not charging the customer the second they click book. I authorize the charge first (hold), capture it once the booking is actually confirmed, and refund it (full or partial, depending on timing) if it's cancelled.

**Async notifications via queues.** Booking confirmed, email goes out. That shouldn't block the API response, so it goes on a queue and a worker handles it in the background.

**Search/filter at scale.** Finding venues by location, date, price, category has to stay fast as the data grows, not just work on 10 test rows.

**Reviews tied to completed bookings.** One can only review a booking that's actually in the `completed` state.

**Two levels of admin access.** Splitting "can see everything" from "can change anything" is its own authorization problem. We don't want someone doing support work to accidentally have the power to issue a refund or approve a vendor.